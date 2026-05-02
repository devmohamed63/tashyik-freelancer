<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ChatMessageStoreRequest;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Services\Gemini\GeminiServiceProviderChatAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServiceProviderChatbotController
{
    public function guestMessage(ChatMessageStoreRequest $request, GeminiServiceProviderChatAgent $agent): JsonResponse
    {
        $guestToken = trim((string) $request->input('guest_token', ''));
        if ($guestToken === '') {
            $guestToken = (string) Str::uuid();
        }

        $conversationUuid = trim((string) $request->input('conversation_uuid', ''));

        if ($conversationUuid !== '') {
            $conversation = AiChatConversation::query()
                ->where('uuid', $conversationUuid)
                ->whereNull('user_id')
                ->where('guest_token', $guestToken)
                ->where('status', 'open')
                ->first();

            if (! $conversation) {
                return response()->json([
                    'message' => 'المحادثة غير موجودة أو انتهت صلاحيتها، أو التوكن لا يطابق هذه المحادثة.',
                ], 404);
            }
        } else {
            $conversation = AiChatConversation::query()
                ->whereNull('user_id')
                ->where('guest_token', $guestToken)
                ->where('status', 'open')
                ->where(function ($query): void {
                    $query->where('meta->channel', 'service_provider_chatbot_guest')
                        ->orWhereNull('meta');
                })
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->first();

            if (! $conversation) {
                $conversation = AiChatConversation::create([
                    'uuid' => (string) Str::uuid(),
                    'guest_token' => $guestToken,
                    'status' => 'open',
                    'last_message_at' => now(),
                    'meta' => ['channel' => 'service_provider_chatbot_guest'],
                ]);
            } else {
                $meta = is_array($conversation->meta) ? $conversation->meta : [];
                if (($meta['channel'] ?? null) !== 'service_provider_chatbot_guest') {
                    $meta['channel'] = 'service_provider_chatbot_guest';
                    $conversation->meta = $meta;
                    $conversation->save();
                }
            }
        }

        return $this->processMessage($conversation, (string) $request->input('message'), true, $guestToken, $agent);
    }

    public function userMessage(ChatMessageStoreRequest $request, GeminiServiceProviderChatAgent $agent): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $conversationUuid = trim((string) $request->input('conversation_uuid', ''));

        if ($conversationUuid !== '') {
            $conversation = AiChatConversation::query()
                ->where('uuid', $conversationUuid)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if (! $conversation) {
                return response()->json([
                    'message' => 'المحادثة غير موجودة أو لا تخص هذا الحساب.',
                ], 404);
            }
        } else {
            $conversation = AiChatConversation::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->where(function ($query): void {
                    $query->where('meta->channel', 'service_provider_chatbot_user')
                        ->orWhereNull('meta');
                })
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->first();

            if (! $conversation) {
                $conversation = AiChatConversation::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'status' => 'open',
                    'last_message_at' => now(),
                    'meta' => ['channel' => 'service_provider_chatbot_user'],
                ]);
            } else {
                $meta = is_array($conversation->meta) ? $conversation->meta : [];
                if (($meta['channel'] ?? null) !== 'service_provider_chatbot_user') {
                    $meta['channel'] = 'service_provider_chatbot_user';
                    $conversation->meta = $meta;
                    $conversation->save();
                }
            }
        }

        return $this->processMessage($conversation, (string) $request->input('message'), false, null, $agent);
    }

    /**
     * قائمة محادثات شات البوت (مسجّل دخول) مرتبة من الأحدث.
     */
    public function userConversations(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

        $paginator = AiChatConversation::query()
            ->where('user_id', $user->id)
            ->where(function ($query): void {
                $query->where('meta->channel', 'service_provider_chatbot_user')
                    ->orWhereNull('meta');
            })
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(function (AiChatConversation $conversation): array {
                return [
                    'uuid' => $conversation->uuid,
                    'status' => $conversation->status,
                    'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                    'messages_count' => (int) $conversation->messages_count,
                    'meta' => $this->redactAiConversationMeta($conversation->meta),
                    'created_at' => $conversation->created_at?->toIso8601String(),
                    'updated_at' => $conversation->updated_at?->toIso8601String(),
                ];
            })
        );

        return response()->json($paginator);
    }

    /**
     * رسائل محادثة شات البوت (مسجّل دخول) — الترتيب من الأقدم للأحدث (مناسب لعرض الشاشة).
     */
    public function userConversationMessages(Request $request, AiChatConversation $conversation): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $this->userOwnsServiceProviderChatbotConversation($user->id, $conversation)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $perPage = min(max((int) $request->input('per_page', 50), 1), 100);

        $paginator = $conversation->messages()
            ->orderBy('id')
            ->paginate($perPage);

        $paginator->setCollection(
            $paginator->getCollection()->map(function (AiChatMessage $message): array {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'content_type' => $message->content_type,
                    'intent' => $message->intent,
                    'meta' => $message->meta,
                    'created_at' => $message->created_at?->toIso8601String(),
                ];
            })
        );

        return response()->json([
            'conversation_uuid' => $conversation->uuid,
            'status' => $conversation->status,
            'meta' => $this->redactAiConversationMeta($conversation->meta),
            'messages' => $paginator,
        ]);
    }

    private function processMessage(
        AiChatConversation $conversation,
        string $userMessage,
        bool $isGuest,
        ?string $guestToken,
        GeminiServiceProviderChatAgent $agent
    ): JsonResponse {
        $authUser = Auth::guard('sanctum')->user();

        AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $authUser?->id,
            'content' => $userMessage,
            'content_type' => 'text',
        ]);

        $reply = $agent->reply($conversation, $userMessage, $isGuest);
        $reply = $this->withFahadWelcomeOnFirstTurn($conversation, $reply);

        $assistantMessage = AiChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
            'content' => (string) ($reply['message'] ?? ''),
            'content_type' => 'text',
            'intent' => (string) ($reply['intent'] ?? 'general_help'),
            'meta' => [
                'normal_answer' => (bool) ($reply['normal_answer'] ?? true),
                'service_ids' => $reply['service_ids'] ?? [],
                'service_provider_ids' => $reply['service_provider_ids'] ?? [],
                'ready_to_login' => (bool) ($reply['ready_to_login'] ?? false),
                'requires_city_selection' => (bool) ($reply['requires_city_selection'] ?? false),
                'registration_step' => $reply['registration_step'] ?? null,
            ],
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'conversation_uuid' => $conversation->uuid,
            'guest_token' => $guestToken,
            'normal_answer' => (bool) ($reply['normal_answer'] ?? true),
            'intent' => (string) ($reply['intent'] ?? 'general_help'),
            'message' => (string) ($reply['message'] ?? ''),
            'services' => $reply['services'] ?? [],
            'service_ids' => $reply['service_ids'] ?? [],
            'service_providers' => $reply['service_providers'] ?? [],
            'service_provider_ids' => $reply['service_provider_ids'] ?? [],
            'ready_to_login' => (bool) ($reply['ready_to_login'] ?? false),
            'login_data' => $reply['login_data'] ?? [
                'username' => '',
                'email' => '',
                'phone' => '',
                'password' => '',
                'city_id' => null,
            ],
            'requires_city_selection' => (bool) ($reply['requires_city_selection'] ?? false),
            'city_options' => $reply['city_options'] ?? [],
            'registration_step' => $reply['registration_step'] ?? null,
            'assistant_message_id' => $assistantMessage->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $reply
     * @return array<string, mixed>
     */
    private function withFahadWelcomeOnFirstTurn(AiChatConversation $conversation, array $reply): array
    {
        $userMessageCount = AiChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_type', 'user')
            ->count();

        if ($userMessageCount !== 1) {
            return $reply;
        }

        $welcome = "هلا وسهلا، معاك فهد.\nأنا في خدمتك في الصيانة المنزلية وترشيح الفنيين.\n\n";

        $body = trim((string) ($reply['message'] ?? ''));
        if ($body !== '' && str_contains($body, 'معاك فهد')) {
            return $reply;
        }

        $reply['message'] = $body !== '' ? $welcome.$body : rtrim($welcome);

        return $reply;
    }

    private function userOwnsServiceProviderChatbotConversation(int $userId, AiChatConversation $conversation): bool
    {
        if ((int) $conversation->user_id !== $userId) {
            return false;
        }

        $meta = is_array($conversation->meta) ? $conversation->meta : [];

        $channel = $meta['channel'] ?? null;

        return $channel === 'service_provider_chatbot_user' || $channel === null;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>
     */
    private function redactAiConversationMeta(?array $meta): array
    {
        if (! is_array($meta)) {
            return [];
        }

        if (isset($meta['login_flow']) && is_array($meta['login_flow'])) {
            $flow = $meta['login_flow'];
            if (array_key_exists('password', $flow) && is_string($flow['password']) && $flow['password'] !== '') {
                $flow['password'] = '[redacted]';
            }
            $meta['login_flow'] = $flow;
        }

        return $meta;
    }
}

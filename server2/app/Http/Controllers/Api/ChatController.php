<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ChatMessageStoreRequest;
use App\Http\Resources\ChatConversationResource;
use App\Http\Resources\ChatMessageResource;
use App\Jobs\GenerateChatReplyJob;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController
{
    public function start(Request $request): JsonResponse
    {
        $authUser = Auth::guard('sanctum')->user();
        $guestToken = trim((string) $request->input('guest_token', ''));

        if ($authUser) {
            $conversation = ChatConversation::query()
                ->where('user_id', $authUser->id)
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if (!$conversation) {
                $conversation = ChatConversation::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $authUser->id,
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);
            }

            return response()->json([
                'conversation' => new ChatConversationResource($conversation),
                'guest_token' => null,
            ]);
        }

        if ($guestToken === '') {
            $guestToken = (string) Str::uuid();
        }

        $conversation = ChatConversation::query()
            ->whereNull('user_id')
            ->where('guest_token', $guestToken)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'uuid' => (string) Str::uuid(),
                'guest_token' => $guestToken,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        return response()->json([
            'conversation' => new ChatConversationResource($conversation),
            'guest_token' => $guestToken,
        ]);
    }

    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        if (!$this->ownsConversation($request, $conversation)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messages = $conversation->messages()->oldest('id')->paginate(50);

        return response()->json([
            'conversation' => new ChatConversationResource($conversation),
            'messages' => ChatMessageResource::collection($messages),
        ]);
    }

    public function sendMessage(ChatMessageStoreRequest $request, ChatConversation $conversation): JsonResponse
    {
        if (!$this->ownsConversation($request, $conversation)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $authUser = Auth::guard('sanctum')->user();

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $authUser?->id,
            'content' => (string) $request->input('message'),
            'content_type' => 'text',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        GenerateChatReplyJob::dispatch($conversation->id);

        return response()->json([
            'queued' => true,
            'message' => new ChatMessageResource($message),
        ], 202);
    }

    public function humanReply(ChatMessageStoreRequest $request, ChatConversation $conversation): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user || !$user->can('manage contact requests')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'human',
            'sender_id' => $user->id,
            'content' => (string) $request->input('message'),
            'content_type' => 'text',
        ]);

        $conversation->assigned_admin_id = $user->id;
        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'message' => new ChatMessageResource($message),
        ], 201);
    }

    private function ownsConversation(Request $request, ChatConversation $conversation): bool
    {
        $authUser = Auth::guard('sanctum')->user();
        if ($authUser) {
            return (int) $conversation->user_id === (int) $authUser->id;
        }

        $guestToken = trim((string) $request->input('guest_token', $request->header('X-Guest-Token', '')));

        return $guestToken !== '' && $conversation->guest_token === $guestToken;
    }
}

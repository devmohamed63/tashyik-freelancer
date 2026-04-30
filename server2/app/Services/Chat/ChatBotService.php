<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatBotService
{
    /**
     * @param Collection<int, \App\Models\ChatMessage> $messages
     * @return array{reply_text:string,intent:string,suggested_service_slug:?string,captured_name:?string,captured_phone:?string,captured_email:?string,suggest_email:bool}
     */
    public function generateReply(ChatConversation $conversation, Collection $messages): array
    {
        $prompt = $this->buildPrompt($conversation, $messages);
        $response = $this->requestGemini($prompt);
        $text = $this->extractCandidateText($response);
        $payload = $this->decodePayload($text);

        if (!is_array($payload)) {
            throw new \RuntimeException('Chat AI response is not valid JSON. Snippet: ' . Str::limit($text, 300));
        }

        $replyText = (string) ($payload['reply_text'] ?? __('ui.success_message'));
        $suggestedSlug = $this->nullableString($payload['suggested_service_slug'] ?? null);

        // Append service link if a service is suggested
        if ($suggestedSlug) {
            $frontendUrl = rtrim((string) config('app.frontend_url', 'https://www.tashyik.com'), '/');
            $serviceLink = "{$frontendUrl}/services/{$suggestedSlug}";
    
            // Check if this specific link was already sent in the conversation history
            $alreadySent = $messages->contains(fn($m) => Str::contains((string) $m->content, $serviceLink));

            // Append only if not already sent and not already in the current AI text
            if (!$alreadySent && !Str::contains($replyText, $serviceLink)) {
                $replyText .= "\n\n" . "يمكنك طلب الخدمة مباشرة من هنا: " . $serviceLink;
            }
        }

        return [
            'reply_text' => $replyText,
            'intent' => (string) ($payload['intent'] ?? 'general'),
            'suggested_service_slug' => $suggestedSlug,
            'captured_name' => $this->nullableString($payload['captured_name'] ?? null),
            'captured_phone' => $this->nullableString($payload['captured_phone'] ?? null),
            'captured_email' => $this->nullableString($payload['captured_email'] ?? null),
            'suggest_email' => (bool) ($payload['suggest_email'] ?? false),
        ];
    }

    /**
     * @param Collection<int, \App\Models\ChatMessage> $messages
     */
    private function buildPrompt(ChatConversation $conversation, Collection $messages): string
    {
        $services = Service::query()
            ->orderBy('id')
            ->limit(60)
            ->get(['id', 'slug', 'name']);

        $servicesList = $services->map(function (Service $service) {
            $ar = $service->getTranslation('name', 'ar', false);
            $en = $service->getTranslation('name', 'en', false);

            return [
                'id' => $service->id,
                'slug' => $service->slug,
                'name_ar' => $ar,
                'name_en' => $en,
            ];
        })->values()->all();

        $history = $messages->map(function ($message) {
            return [
                'sender' => $message->sender_type,
                'text' => Str::limit((string) $message->content, 1000, '...'),
            ];
        })->values()->all();

        $registrationData = [
            'lead_name' => $conversation->lead_name,
            'lead_phone' => $conversation->lead_phone,
            'lead_email' => $conversation->lead_email,
            'registration_completed' => (bool) $conversation->registration_completed,
            'has_account' => $conversation->user_id !== null,
        ];

        $isFirstAiResponse = !$messages->contains('sender_type', 'ai');

        return "You are Tashyik support assistant.\n"
            . "Important behavior rules:\n"
            . "- Write naturally in Arabic unless user writes in English.\n"
            . ($isFirstAiResponse ? "- This is your FIRST response. Include a friendly greeting like 'مرحباً بك في تشييك' at the beginning of your message, then proceed to answer the user's request.\n" : "")
            . "- Never reveal whether the responder is AI or human.\n"
            . "- If user asks for a service, suggest exactly one matching service slug from available services.\n"
            . "- If user is NOT registered (has_account is false), politely invite them to create an account to track their orders and manage services.\n"
            . "- If they agree or ask how to register, ask for their (Full Name, Phone Number, and Email) and explain that this will automatically create an account for them.\n"
            . "- Extract user details (name/phone/email) from messages whenever they appear, even if the message contains ONLY the detail.\n"
            . "- Keep response concise.\n"
            . "Return ONLY strict JSON with keys:\n"
            . "reply_text,intent,suggested_service_slug,captured_name,captured_phone,captured_email,suggest_email\n"
            . "intent must be one of: general,faq,service_request,lead_capture,registration_help\n"
            . "use null for unknown captured fields.\n"
            . "Context:\n"
            . json_encode([
                'conversation_registration' => $registrationData,
                'available_services' => $servicesList,
                'chat_history' => $history,
            ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestGemini(string $prompt): array
    {
        $base = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $apiKey = (string) config('services.gemini.api_key');

        $primaryModel = trim((string) config('services.gemini.chat_model', config('services.gemini.text_model', 'gemini-2.5-flash')));
        $primaryModel = preg_replace('#^models/#', '', $primaryModel) ?: 'gemini-2.5-flash';

        $fallbackModels = (array) config('services.gemini.text_models', []);
        $modelsToTry = array_unique(array_filter(array_merge([$primaryModel], $fallbackModels)));

        $lastResponse = null;

        foreach ($modelsToTry as $model) {
            $model = preg_replace('#^models/#', '', $model);
            $url = "{$base}/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::acceptJson()
                    ->timeout(120)
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'maxOutputTokens' => max(512, (int) config('services.gemini.chat_max_output_tokens', 2048)),
                            'temperature' => (float) config('services.gemini.chat_temperature', 0.4),
                        ],
                    ]);

                if ($response->successful()) {
                    return (array) $response->json();
                }

                $lastResponse = $response;

                // Retry on temporary errors (503 Service Unavailable, 429 Too Many Requests, or 500 Server Error)
                if (!in_array($response->status(), [429, 500, 503])) {
                    break;
                }
            } catch (\Throwable $e) {
                // In case of networking errors, we can also try the next model
                if ($model === end($modelsToTry)) {
                    throw $e;
                }
            }
        }

        $status = $lastResponse?->status() ?? 'Unknown';
        $body = $lastResponse?->body() ?? 'No response body';

        throw new \RuntimeException('Chat AI request failed after trying available models. Last status: ' . $status . ' Body: ' . Str::limit($body, 300));
    }

    /**
     * @param array<string, mixed> $apiResponse
     */
    private function extractCandidateText(array $apiResponse): string
    {
        $parts = data_get($apiResponse, 'candidates.0.content.parts', []);
        if (!is_array($parts)) {
            return '';
        }

        $chunks = [];
        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        return trim(implode("\n", $chunks));
    }

    /**
     * @return array<string,mixed>|null
     */
    private function decodePayload(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```\s*$/', '', $raw) ?? $raw;
        $raw = trim($raw);

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}

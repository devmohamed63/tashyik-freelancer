<?php

namespace App\Jobs;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\Chat\ChatBotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class GenerateChatReplyJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $conversationId)
    {
    }

    public function handle(ChatBotService $chatBot): void
    {
        $conversation = ChatConversation::query()->find($this->conversationId);
        if (!$conversation || $conversation->status !== 'open') {
            return;
        }

        $messages = $conversation->messages()
            ->latest('id')
            ->limit(20)
            ->get()
            ->sortBy('id')
            ->values();

        if ($messages->isEmpty()) {
            return;
        }

        try {
            $reply = $chatBot->generateReply($conversation, $messages);
        } catch (\Throwable $e) {
            Log::warning('GenerateChatReplyJob failed', ['error' => $e->getMessage()]);

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'system',
                'content' => 'تعذر إرسال الرد الآن، برجاء المحاولة مرة أخرى بعد قليل.',
                'intent' => 'general',
            ]);

            return;
        }

        // Extract and update lead information if provided by AI
        if ($name = $this->cleanName($reply['captured_name'] ?? null)) {
            $conversation->lead_name = $name;
        }
        if ($phone = $this->normalizePhone($reply['captured_phone'] ?? null)) {
            $conversation->lead_phone = $phone;
        }
        if ($email = $this->normalizeEmail($reply['captured_email'] ?? null)) {
            $conversation->lead_email = $email;
        }

        $conversation->last_message_at = now();
        $conversation->save();

        $meta = [
            'intent' => $reply['intent'],
            'suggested_service_slug' => $reply['suggested_service_slug'],
            'suggest_email' => (bool) ($reply['suggest_email'] ?? false),
        ];

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
            'content' => (string) $reply['reply_text'],
            'intent' => (string) $reply['intent'],
            'meta' => $meta,
        ]);

        $this->tryCreateUserAndSendPasswordSetup($conversation);
    }

    private function tryCreateUserAndSendPasswordSetup(ChatConversation $conversation): void
    {
        if ($conversation->registration_completed || $conversation->user_id) {
            return;
        }

        $name = $this->cleanName($conversation->lead_name);
        $phone = $this->normalizePhone($conversation->lead_phone);
        $email = $this->normalizeEmail($conversation->lead_email);

        if (!$name || !$phone || !$email) {
            return;
        }

        $existing = User::query()
            ->where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        if ($existing) {
            $conversation->user_id = $existing->id;
            $conversation->registration_completed = true;
            $conversation->save();

            return;
        }

        try {
            $user = User::create([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'type' => User::USER_ACCOUNT_TYPE,
                'status' => User::ACTIVE_STATUS,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create chat user account', ['error' => $e->getMessage()]);

            return;
        }

        $conversation->user_id = $user->id;
        $conversation->registration_completed = true;
        $conversation->save();

        Password::sendResetLink(['email' => $user->email]);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'system',
            'content' => 'تم إنشاء حسابك بنجاح وإرسال رابط تعيين كلمة المرور إلى بريدك الإلكتروني. بعد ضبط كلمة المرور يمكنك تسجيل الدخول مباشرة.',
            'intent' => 'registration_help',
        ]);
    }

    private function cleanName(?string $name): ?string
    {
        if (!$name || !is_string($name)) {
            return null;
        }

        $name = trim($name);

        return $name === '' ? null : Str::limit($name, 255, '');
    }

    private function normalizeEmail(?string $email): ?string
    {
        if (!$email || !is_string($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone || !is_string($phone)) {
            return null;
        }

        $phone = trim($phone);
        $phone = preg_replace('/[^\d\+]/', '', $phone) ?? '';

        if ($phone === '' || strlen($phone) < 8 || strlen($phone) > 20) {
            return null;
        }

        return $phone;
    }
}

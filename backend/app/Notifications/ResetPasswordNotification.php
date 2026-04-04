<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url');
        $locale = $notifiable->ui_locale ?? config('app.locale', 'ar');

        $url = "{$frontendUrl}/{$locale}/reset-password?token={$this->token}&email=" . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject(__('passwords.reset_password_subject', [], $locale))
            ->greeting(__('passwords.greeting', ['name' => $notifiable->name], $locale))
            ->line(__('passwords.reset_password_line_1', [], $locale))
            ->action(__('passwords.reset_password_action', [], $locale), $url)
            ->line(__('passwords.reset_password_line_2', ['minutes' => config('auth.passwords.users.expire')], $locale))
            ->line(__('passwords.reset_password_line_3', [], $locale))
            ->salutation(__('passwords.salutation', ['app' => config('app.name')], $locale));
    }
}

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
     * Indicates if the request comes from the Dashboard.
     */
    public bool $isDashboard;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token, bool $isDashboard = false)
    {
        $this->token = $token;
        $this->isDashboard = $isDashboard;
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
        $locale = $notifiable->ui_locale ?? config('app.locale', 'ar');

        if ($this->isDashboard) {
            // Generates absolute URL with dashboard domain, e.g. http://dashboard.localhost/reset-password/{token}?email=...
            $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);
        } else {
            $frontendUrl = config('app.frontend_url');
            $url = "{$frontendUrl}/{$locale}/reset-password?token={$this->token}&email=" . urlencode($notifiable->email);
        }

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

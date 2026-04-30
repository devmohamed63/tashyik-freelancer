<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject(__('passwords.otp_subject', ['app' => $appName]))
            ->greeting(__('passwords.otp_greeting'))
            ->line(__('passwords.otp_intro'))
            ->line("## {$this->otp}")
            ->line(__('passwords.otp_expiry'))
            ->line(__('passwords.otp_ignore'))
            ->salutation(__('passwords.otp_salutation', ['app' => $appName]));
    }
}

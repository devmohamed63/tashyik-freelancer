<?php

namespace App\Utils\Traits\Listeners;

use App\Models\User;
use App\Models\Notification;
use App\Utils\Services\Firebase\CloudMessaging;

trait HasNotifications
{
    protected function sendNotification(bool $pushNotification, User $recipient, array $title, string $type, array $data = [], $description = null)
    {
        $notification = new Notification([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'data' => !empty($data) ? json_encode($data) : null
        ]);

        $recipient->notifications()->save($notification);

        // Send web push notification
        if ($pushNotification && array_key_exists($recipient->ui_locale, $title) && $recipient->fcm_token) {
            $body = $description ? $description[$recipient->ui_locale] : '';

            $fcm = new CloudMessaging();
            $fcm->setNotification($title[$recipient->ui_locale], $body);
            $fcm->setData(['notification_type' => $type]);
            $fcm->setTokens([$recipient->fcm_token]);
            $fcm->send();
        }
    }
}

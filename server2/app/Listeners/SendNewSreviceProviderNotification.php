<?php

namespace App\Listeners;

use App\Events\NewUser;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewSreviceProviderNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewUser $event): void
    {
        // Check if registered account is service provider
        if ($event->user->type == User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            $title =  [
                'ar' => 'يوجد مقدم خدمة جديد',
                'en' => 'New service provider.',
            ];

            $notification = new Notification([
                'title' => $title,
                'url' => route('dashboard.users.service_providers', ['showResult' => $event->user->id]),
                'view' => 'new-service-provider'
            ]);

            $notification->save();
        }
    }
}

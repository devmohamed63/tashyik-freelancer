<?php

namespace App\Listeners;

use App\Events\NewPayoutRequest;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPayoutRequestNotification
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
    public function handle(NewPayoutRequest $event): void
    {
        $title =  [
            'ar' => 'يوجد طلب دفع جديد',
            'en' => 'New payout request.',
        ];

        $notification = new Notification([
            'title' => $title,
            'url' => route('dashboard.users.payout_requests', ['showResult' => $event->payoutRequest->id]),
            'view' => 'new-payout-request'
        ]);

        $notification->save();
    }
}

<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\Subscription;
use App\Events\NewUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddTrialSubscription
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
        $user = $event->user;

        // Check if the registered account is service provider
        if ($user->type == User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            // Add trial subscription for one month
            $subscription = new Subscription();
            $subscription->user_id = $user->id;
            $subscription->starts_at = now();
            $subscription->ends_at = now()->addMonth();
            $subscription->save();
        }
    }
}

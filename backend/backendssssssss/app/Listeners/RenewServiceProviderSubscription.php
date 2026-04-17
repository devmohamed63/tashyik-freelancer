<?php

namespace App\Listeners;

use App\Events\PlanPaid;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RenewServiceProviderSubscription
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
    public function handle(PlanPaid $event): void
    {
        $data = $event->data;

        try {
            $serviceProvider = User::find($data['service_provider_id']);

            // Deduct the amount from the user's wallet.
            $serviceProvider?->useWalletBalance($data['wallet_balance']);

            $subscription = new Subscription();
            $subscription->user_id = $data['service_provider_id'];
            $subscription->plan_id = $data['plan_id'];
            $subscription->paid_amount = $data['paid_amount'];
            $subscription->starts_at = $data['starts_at'];
            $subscription->ends_at = $data['ends_at'];

            $serviceProvider->subscription()->delete();
            $serviceProvider->subscription()->save($subscription);
        } catch (\Throwable $th) {
            throw new \Exception("Failed to renew subscription for service provider: $th");
        }
    }
}

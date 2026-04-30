<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\OrderExtra;
use App\Events\OrderExtraPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateOrderExtraStatus
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
    public function handle(OrderExtraPaid $event): void
    {
        $data = $event->data;

        try {
            $user = User::find($data['user_id']);

            // Deduct the amount from the user's wallet.
            $user?->useWalletBalance($data['wallet_balance']);

            $orderExtra = OrderExtra::find($data['order_extra_id']);

            $orderExtra->update([
                'status' => OrderExtra::PAID_STATUS,
                'wallet_balance' => $data['wallet_balance'],
                'total' => $data['total'],
            ]);
        } catch (\Throwable $th) {
            throw new \Exception("Failed to update order extra status: $th");
        }
    }
}

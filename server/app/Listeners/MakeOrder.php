<?php

namespace App\Listeners;

use App\Events\NewOrder;
use App\Events\OrderPaid;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class MakeOrder
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
    public function handle(OrderPaid $event): void
    {
        $data = $event->data;

        try {
            $user = User::find($data['user_id']);

            // Deduct the amount from the user's wallet.
            $user?->useWalletBalance($data['wallet_balance']);

            // Check if user has used the welcome coupon
            $used_welcome_coupon = $data['used_welcome_coupon'] ?? false;

            if ($used_welcome_coupon) $user->update([
                'used_welcome_coupon' => true
            ]);

            $address = Address::find($data['address_id']);

            $order = new Order;
            $order->customer_id = $data['user_id'];
            $order->address_id = $data['address_id'];
            $order->latitude = $address?->latitude;
            $order->longitude = $address?->longitude;
            $order->category_id = $data['category_id'];
            $order->service_id = $data['service_id'];
            $order->description = $data['description'];
            $order->quantity = $data['quantity'];
            $order->visit_cost = $data['visit_cost'];
            $order->subtotal = $data['subtotal'];
            $order->tax_rate = $data['tax_rate'];
            $order->tax = $data['tax'];
            $order->coupons_total = $data['coupons_total'];
            $order->wallet_balance = $data['wallet_balance'];
            $order->total = $data['total'];
            $order->save();

            NewOrder::dispatch($order);
        } catch (\Throwable $th) {
            throw new \Exception("Failed to create order: $th");
        }
    }
}

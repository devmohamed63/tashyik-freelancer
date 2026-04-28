<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Order;
use App\Models\OrderExtra;
use App\Events\OrderExtraPaid;
use App\Http\Resources\OrderExtraResource;
use App\Utils\Http\Controllers\ApiController;
use App\Utils\Services\Paymob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderExtraController extends ApiController
{
    public function index(Request $request)
    {
        $request->validate([
            'order' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $orderExtras = OrderExtra::where('order_id', $request->order)
            ->orderByDesc('id')
            ->get([
                'id',
                'service_id',
                'status',
                'created_at'
            ]);

        return OrderExtraResource::collection($orderExtras);
    }

    public function show(OrderExtra $orderExtra, Request $request)
    {
        $request->validate([
            'confirm_order' => ['required', 'boolean'],
        ]);

        $user = Auth::user();

        if ($orderExtra->status == OrderExtra::PENDING_STATUS) {
            $total = $orderExtra->price + $orderExtra->materials;

            $walletBalance = $user->useWalletBalance($total, false);

            $total = $walletBalance['required_amount'];

            $paymobData = [
                'type' => 'order_extra_paid',
                'user_id' => $user->id,
                'order_id' => $orderExtra->order_id,
                'service_provider_id' => $orderExtra->order?->serviceProvider?->id,
                'order_extra_id' => $orderExtra->id,
                'wallet_balance' => $walletBalance['deducted_amount'],
                ...compact('total'),
            ];

            if ($total == 0 && $request->confirm_order) {
                OrderExtraPaid::dispatch($paymobData);

                return response('');
            }

            if ($total > 0) {
                $paymob = new Paymob();
                $paymob->setReference($paymobData);
                $paymentLink = $paymob->getPaymentLink($total);
            }
        }

        return response()->json([
            'id' => $orderExtra->id,
            'name' => $orderExtra->service?->name,
            'status' => $orderExtra->status,
            'price' => $orderExtra->price,
            'tax_rate' => (float) config('app.tax_rate'),
            'tax' => number_format($orderExtra->tax, config('app.decimal_places')),
            'materials' => number_format($orderExtra->materials, config('app.decimal_places')),
            'wallet_balance' => $orderExtra->wallet_balance ?? $walletBalance['deducted_amount'],
            'total' => number_format($orderExtra->total ?? $walletBalance['required_amount'], config('app.decimal_places')),
            'currency' => __('ui.currency'),
            'payment_link' => $paymentLink ?? null,
        ]);
    }
}

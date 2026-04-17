<?php

namespace App\Http\Controllers\Api;

use App\Events\PlanPaid;
use App\Models\Plan;
use App\Models\Subscription;
use App\Http\Resources\PlanResource;
use App\Http\Resources\SubscriptionResource;
use App\Utils\Traits\HasTax;
use App\Utils\Services\Paymob;
use App\Utils\Http\Controllers\ApiController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class PlanController extends ApiController
{
    use HasTax;

    public function index()
    {
        $serviceProvider = Auth::user();

        $plans = Plan::when($serviceProvider->entity_type, function ($query) use ($serviceProvider) {
                $query->where('target_group', $serviceProvider->entity_type);
            })
            ->orderBy('price')
            ->get([
                'id',
                'name',
                'price',
                'duration_in_days',
            ]);

        $subscription = $serviceProvider->subscription ?? new Subscription();

        return [
            'subscription' => new SubscriptionResource($subscription),
            'plans' => PlanResource::collection($plans),
        ];
    }

    public function show(Plan $plan, Request $request)
    {
        $serviceProvider = Auth::user();

        $tax = $this->getTaxes($plan->price);

        $subtotal = $plan->price + $tax;

        $walletBalance = $serviceProvider->useWalletBalance($subtotal, false);

        $total = $walletBalance['required_amount'];

        $paymobData = [
            'type' => 'plan_paid',
            'service_provider_id' => $serviceProvider->id,
            'plan_id' => $plan->id,
            'paid_amount' => $subtotal,
            'wallet_balance' => $walletBalance['deducted_amount'],
            'starts_at' => Carbon::now()->startOfDay(),
            'ends_at' => Carbon::now()->addDays($plan->duration_in_days)->startOfDay(),
        ];

        if ($total == 0 && $request->confirm_order) {
            PlanPaid::dispatch($paymobData);

            return response('', 200);
        }

        if ($total > 0) {
            $paymob = new Paymob();
            $paymob->setReference($paymobData);
            $paymentLink = $paymob->getPaymentLink($total);
        }

        return response()->json([
            'id' => $plan->id,
            'duration' => $plan->duration_in_months,
            'price' => $plan->price,
            'tax_rate' => config('app.tax_rate'),
            ...compact('tax'),
            'wallet_balance' => $walletBalance['deducted_amount'],
            ...compact('total'),
            'currency' => __('ui.currency'),
            'payment_link' => $paymentLink ?? null,
        ]);
    }
}

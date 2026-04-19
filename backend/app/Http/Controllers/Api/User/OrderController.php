<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Order;
use App\Models\Coupon;
use App\Models\Service;
use App\Events\OrderPaid;
use App\Utils\Traits\HasTax;
use App\Utils\Services\Paymob;
use App\Utils\Http\Controllers\ApiController;
use App\Http\Resources\Customer\OrderResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Carbon\CarbonInterval;
use App\Jobs\SyncCreditNoteToDaftra;

class OrderController extends ApiController
{
    use HasTax;

    public function store(Request $request)
    {
        $request->validate([
            'address' => ['nullable', 'integer', 'exists:addresses,id'],
            'service' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1'],
            'coupons' => ['nullable', 'array'],
            'coupons.*' => ['string', 'max:255'],
            'confirm_order' => ['required', 'boolean'],
        ]);

        $user = Auth::user();

        $service = is_numeric($request->service)
            ? Service::findOrFail($request->service)
            : Service::where('slug', $request->service)->firstOrFail();

        $visit_cost = $service->getVisitCost();
        $subtotal = ($service->price * $request->quantity) + $visit_cost;
        $tax = $this->getTaxes($subtotal);

        $originalTotal = $subtotal + $tax;

        $usedWelcomeCoupon = false;

        // Apply coupons
        if ($request->coupons) {
            $couponsTotal = 0;

            $appliedCoupons = array_map(function ($code) use (&$usedWelcomeCoupon, &$originalTotal, &$couponsTotal, $service) {
                $coupon = Coupon::where('code', $code)->first();

                $isValid = (bool) $coupon?->isValid($service);

                if ($isValid) {
                    switch ($coupon->type) {
                        case Coupon::PERCENTAGE_TYPE:
                            $value = round($originalTotal * ($coupon->value / 100), 2);
                            break;

                        case Coupon::FIXED_TYPE:
                            $value = round($coupon->value, 2);
                            break;
                    }

                    // Prevent the minus amount
                    $value = max(0, $value);

                    // Increase usage times
                    $coupon->increment('usage_times');

                    // Decrease total value
                    $originalTotal -= $value;

                    $couponsTotal += $value;

                    // Check if user has used the welcome coupon
                    $usedWelcomeCoupon = $coupon->welcome == true;
                }

                return [
                    'code' => $code,
                    'is_valid' => $isValid,
                    'amount' => ($value ?? 0) . ' ' . __('ui.currency')
                ];
            }, array_unique(array_values($request->coupons)));
        }

        $walletBalance = $user->useWalletBalance($originalTotal, false);

        $total = $walletBalance['required_amount'];

        $paymobData = [
            'type' => 'order_paid',
            'user_id' => $user->id,
            'address_id' => $request->address,
            'category_id' => $service->category?->category_id,
            'service_id' => $service->id,
            'description' => $request->description,
            'quantity' => $request->quantity,
            ...compact('visit_cost', 'subtotal'),
            'tax_rate' => config('app.tax_rate'),
            ...compact('tax'),
            'coupons_total' => min($originalTotal, $couponsTotal ?? 0),
            'wallet_balance' => $walletBalance['deducted_amount'],
            ...compact('total'),
            // Check if user has used the welcome coupon
            'used_welcome_coupon' => $usedWelcomeCoupon
        ];

        if ($total == 0 && $request->confirm_order) {
            OrderPaid::dispatch($paymobData);

            return response('', 201);
        }

        if ($total > 0) {
            $paymob = new Paymob();
            $paymob->setReference($paymobData);
            $paymentLink = $paymob->getPaymentLink($total);
        }

        return response()->json([
            'service' => [
                'id' => $service->id,
                'slug' => $service->slug,
                'name' => $service->name,
            ],
            ...compact('visit_cost', 'subtotal'),
            'tax_rate' => (float) config('app.tax_rate'),
            'tax' => number_format($tax, config('app.decimal_places')),
            'coupons' => $appliedCoupons ?? [],
            'wallet_balance' => round($walletBalance['deducted_amount'], 2),
            'total' => number_format($total, config('app.decimal_places')),
            'currency' => __('ui.currency'),
            'payment_link' => $paymentLink ?? null,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:new,in_progress,completed']
        ]);

        $customer = Auth::user();

        $query = $customer->orders()
            ->with(['service:id,name'])
            ->orderByDesc('id');

        switch ($request->status) {
            case 'new':
                $query->isNew();
                break;

            case 'in_progress':
                $query->whereIn('status', [
                    Order::SERVICE_PROVIDER_ON_THE_WAY,
                    Order::SERVICE_PROVIDER_ARRIVED,
                    Order::STARTED_STATUS,
                ]);
                break;

            case 'completed':
                $query->completed();
                break;
        }

        $orders = $query->paginate($this->paginationLimit, [
            'id',
            'service_id',
            'status',
            'total',
            'created_at',
        ]);

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $serviceProviderMapUrl = $order->serviceProvider
            ? "https://maps.google.com/maps?q={$order->serviceProvider->latitude},{$order->serviceProvider->longitude}"
            : null;

        return [
            'id' => $order->id,
            'service' => [
                'id' => $order->service?->id,
                'slug' => $order->service?->slug,
                'name' => $order->service?->name,
            ],
            'service_provider' => [
                'id' => $order->serviceProvider?->id,
                'name' => $order->serviceProvider?->name,
                'phone' => $order->serviceProvider?->phone,
                'map_url' => $serviceProviderMapUrl,
            ],
            'status' => $order->status,
            'can_cancel' => $order->is_cancelable,
            'can_review' => $order->is_reviewable,
            'description' => $order->description,
            'quantity' => $order->quantity,
            'visit_cost' => $order->visit_cost,
            'subtotal' => number_format($order->subtotal, config('app.decimal_places')),
            'tax_rate' => $order->tax_rate,
            'tax' => number_format($order->tax, config('app.decimal_places')),
            'coupons_total' => number_format($order->coupons_total, config('app.decimal_places')),
            'wallet_balance' => number_format($order->wallet_balance, config('app.decimal_places')),
            'total' => number_format($order->total, config('app.decimal_places')),
            'currency' => __('ui.currency'),
            'created_at' => $order->created_at->isoFormat(config('app.time_format')),
        ];
    }

    public function destroy(Order $order)
    {
        Gate::authorize('cancel', $order);

        $user = Auth::user();

        $key = "cancel-orders:$user->id";

        $executed = RateLimiter::attempt($key, 1, function () use ($order, $user, $key) {
            // Calculate the paid amount and the balance deducted from the wallet.
            $amount = $order->total + $order->wallet_balance;

            $user->increment('balance', $amount);

            // Sync credit note to Daftra (capture data before deletion)
            if ($order->subtotal > 0) {
                SyncCreditNoteToDaftra::dispatch(
                    customerId: $order->customer_id,
                    serviceName: $order->service?->getTranslation('name', 'ar') ?? 'طلب خدمة',
                    subtotal: (float) $order->subtotal,
                    taxRate: (float) ($order->tax_rate ?? config('app.tax_rate', 15)),
                    orderId: $order->id,
                );
            }

            $order->delete();
        },  now()->addMinutes(30));

        if (!$executed) {
            $seconds = RateLimiter::availableIn($key);

            $availableIn = CarbonInterval::seconds($seconds)
                ->cascade()
                ->forHumans(['parts' => 1]);

            abort(403, __('ui.many_cancel_attempts', [
                'available_in' => $availableIn
            ]));
        }

        return response('');
    }
}

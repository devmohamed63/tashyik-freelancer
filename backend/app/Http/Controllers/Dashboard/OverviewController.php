<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OverviewController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('view dashboard');

        $overviewData = Cache::remember('dashboard-overview', now()->endOfHour(), function () {

            // ── Counts ──────────────────────────────────────────
            $usersCount                = number_format(User::isUser()->count());
            $serviceProvidersCount     = number_format(User::notUser()->count());
            $activeSubscriptionsCount  = number_format(Subscription::query()->active()->count());
            $inactiveSubscriptionsCount = number_format(Subscription::query()->inactive()->count());
            $payoutRequestsCount       = number_format(PayoutRequest::count());
            $newOrdersCount            = number_format(Order::isNew()->count());
            $onProgressOrdersCount     = number_format(Order::started()->count());
            $completedOrdersCount      = number_format(Order::completed()->count());
            $contactRequestCount       = number_format(Contact::count());

            // ── Revenue ─────────────────────────────────────────
            $decimal = config('app.decimal_places');

            $revenueToday = number_format(
                Order::completed()->whereDate('updated_at', today())->sum('subtotal'),
                $decimal
            );
            $revenueWeek = number_format(
                Order::completed()->whereBetween('updated_at', [now()->startOfWeek(), now()])->sum('subtotal'),
                $decimal
            );
            $revenueMonth = number_format(
                Order::completed()->whereYear('updated_at', now()->year)->whereMonth('updated_at', now()->month)->sum('subtotal'),
                $decimal
            );
            $revenueTotal = number_format(
                Order::completed()->sum('subtotal'),
                $decimal
            );

            // ── Tax & Payouts ───────────────────────────────────
            $taxCollected = number_format(
                Order::completed()->sum('tax'),
                $decimal
            );
            $pendingPayouts = number_format(
                User::where('type', '!=', User::USER_ACCOUNT_TYPE)->sum('balance'),
                $decimal
            );

            // ── Temporal Orders ─────────────────────────────────
            $ordersToday = number_format(Order::whereDate('created_at', today())->count());
            $ordersWeek  = number_format(Order::whereBetween('created_at', [now()->startOfWeek(), now()])->count());
            $ordersMonth = number_format(
                Order::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count()
            );

            // ── Charts: Service Providers ───────────────────────
            $serviceProviderCategories = Category::isParent()
                ->withCount('serviceProviders')
                ->get(['id', 'name']);

            $serviceProviderCities = User::notUser()
                ->whereNotNull('city_id')
                ->with('city:id,name')
                ->select(DB::raw('count(*) as count'), 'city_id')
                ->groupBy('city_id')
                ->get();

            // ── Charts: Revenue & Orders by Category ────────────
            $categoryRevenueSubquery = Order::selectRaw('COALESCE(SUM(subtotal), 0)')
                ->where('orders.status', Order::COMPLETED_STATUS)
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('services')
                      ->whereColumn('services.id', 'orders.service_id')
                      ->join('categories as subcats', 'subcats.id', '=', 'services.category_id')
                      ->whereColumn('subcats.category_id', 'categories.id');
                });

            $categoryOrdersSubquery = Order::selectRaw('COUNT(orders.id)')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('services')
                      ->whereColumn('services.id', 'orders.service_id')
                      ->join('categories as subcats', 'subcats.id', '=', 'services.category_id')
                      ->whereColumn('subcats.category_id', 'categories.id');
                });

            $revenueByCategory = Category::isParent()
                ->addSelect(['revenue' => $categoryRevenueSubquery])
                ->get(['id', 'name']);

            $ordersByCategory = Category::isParent()
                ->addSelect(['total_orders' => $categoryOrdersSubquery])
                ->get(['id', 'name']);

            return compact(
                'usersCount',
                'serviceProvidersCount',
                'activeSubscriptionsCount',
                'inactiveSubscriptionsCount',
                'payoutRequestsCount',
                'newOrdersCount',
                'onProgressOrdersCount',
                'completedOrdersCount',
                'contactRequestCount',

                // Revenue
                'revenueToday',
                'revenueWeek',
                'revenueMonth',
                'revenueTotal',

                // Tax & Payouts
                'taxCollected',
                'pendingPayouts',

                // Temporal Orders
                'ordersToday',
                'ordersWeek',
                'ordersMonth',

                // Charts
                'serviceProviderCategories',
                'serviceProviderCities',
                'revenueByCategory',
                'ordersByCategory',
            );
        });

        return view('dashboard.overview.index', $overviewData);
    }
}

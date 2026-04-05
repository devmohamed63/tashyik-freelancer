<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Service;
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

            // ── NEW: Alert Cards ────────────────────────────────
            $pendingProvidersCount = User::isServiceProvider()->where('status', User::PENDING_STATUS)->count();
            $expiringSubscriptionsCount = Subscription::query()
                ->whereBetween('ends_at', [now(), now()->addDays(7)])
                ->count();
            $unreadContactsCount = Contact::where('is_read', false)->count();
            $staleNewOrdersCount = Order::isNew()
                ->where('created_at', '<', now()->subHour())
                ->count();

            // ── NEW: Additional KPIs ────────────────────────────
            $avgOrderValue = number_format(
                Order::completed()->avg('subtotal') ?? 0,
                $decimal
            );
            $couponsUsedCount = number_format(Coupon::sum('usage_times'));
            $totalDiscountGiven = number_format(
                Order::completed()->sum('coupons_total'),
                $decimal
            );

            // ── NEW: Latest 5 Orders ────────────────────────────
            $latestOrders = Order::latest()
                ->with([
                    'customer' => fn($q) => $q->withTrashed()->select('id', 'name'),
                    'service:id,name',
                    'serviceProvider' => fn($q) => $q->withTrashed()->select('id', 'name'),
                ])
                ->take(5)
                ->get(['id', 'customer_id', 'service_provider_id', 'service_id', 'subtotal', 'status', 'created_at']);

            // ── NEW: Top 5 Services ─────────────────────────────
            $topServices = Service::withCount('orders')
                ->orderByDesc('orders_count')
                ->take(5)
                ->get(['id', 'name']);

            // ── NEW: Top 5 Providers ────────────────────────────
            $topProviders = User::isServiceProvider()
                ->withCount('serviceProviderOrders')
                ->withSum(
                    ['serviceProviderOrders as revenue' => fn($q) => $q->where('status', Order::COMPLETED_STATUS)],
                    'subtotal'
                )
                ->with(['categories:id,name', 'city:id,name'])
                ->orderByDesc('service_provider_orders_count')
                ->take(5)
                ->get(['id', 'name', 'phone', 'entity_type', 'status', 'city_id', 'balance']);

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

                // Alert Cards
                'pendingProvidersCount',
                'expiringSubscriptionsCount',
                'unreadContactsCount',
                'staleNewOrdersCount',

                // Additional KPIs
                'avgOrderValue',
                'couponsUsedCount',
                'totalDiscountGiven',

                // Tables
                'latestOrders',
                'topServices',
                'topProviders',

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


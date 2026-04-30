<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CityController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', City::class);

        return view('dashboard.cities.index');
    }

    public function show(City $city)
    {
        Gate::authorize('view', $city);

        $decimal = config('app.decimal_places');

        // ── Summary Stats ────────────────────────────────────
        $totalServiceProviders = User::isServiceProvider()->where('city_id', $city->id)->count();
        $totalUsers = User::isUser()->where('city_id', $city->id)->count();
        $pendingProviders = User::isServiceProvider()->where('city_id', $city->id)->where('status', User::PENDING_STATUS)->count();
        $activeProviders = User::isServiceProvider()->where('city_id', $city->id)->where('status', User::ACTIVE_STATUS)->count();
        $inactiveProviders = User::isServiceProvider()->where('city_id', $city->id)->where('status', User::INACTIVE_STATUS)->count();

        // ── Entity Type Breakdown ────────────────────────────
        $individualProviders = User::isServiceProvider()->where('city_id', $city->id)->isIndividual()->count();
        $institutionProviders = User::isServiceProvider()->where('city_id', $city->id)->isInstitution()->count();
        $companyProviders = User::isServiceProvider()->where('city_id', $city->id)->isCompany()->count();

        // ── Orders (through service providers in this city) ──
        $providerIds = User::isServiceProvider()->where('city_id', $city->id)->pluck('id');

        // Reusable city scope for orders
        $cityOrderScope = function ($q) use ($providerIds, $city) {
            $q->whereIn('service_provider_id', $providerIds)
                ->orWhereHas('customer', fn($q2) => $q2->where('city_id', $city->id));
        };

        $totalOrders = Order::where($cityOrderScope)->count();

        $completedOrders = Order::completed()->where($cityOrderScope)->count();

        $newOrders = Order::isNew()
            ->whereHas('customer', fn($q) => $q->where('city_id', $city->id))
            ->count();

        $onTheWayOrders = Order::serviceProviderOnTheWay()->where($cityOrderScope)->count();

        $arrivedOrders = Order::serviceProviderArrived()->where($cityOrderScope)->count();

        $startedOrders = Order::started()->where($cityOrderScope)->count();

        // ── Revenue ──────────────────────────────────────────
        $revenueQuery = Order::completed()->where($cityOrderScope);

        $revenueToday = number_format(
            (clone $revenueQuery)->whereDate('updated_at', today())->sum('subtotal'),
            $decimal
        );
        $revenueWeek = number_format(
            (clone $revenueQuery)->whereBetween('updated_at', [now()->startOfWeek(), now()])->sum('subtotal'),
            $decimal
        );
        $revenueMonth = number_format(
            (clone $revenueQuery)->whereYear('updated_at', now()->year)->whereMonth('updated_at', now()->month)->sum('subtotal'),
            $decimal
        );
        $revenueTotal = number_format(
            (clone $revenueQuery)->sum('subtotal'),
            $decimal
        );
        $avgOrderValue = number_format(
            (clone $revenueQuery)->avg('subtotal') ?? 0,
            $decimal
        );

        // ── Top 5 Service Providers ──────────────────────────
        // Providers registered in city OR who served customers in city
        $topProviders = User::isServiceProvider()
            ->where(function ($q) use ($city) {
                $q->where('city_id', $city->id)
                  ->orWhereHas('serviceProviderOrders.customer', fn($q2) => $q2->where('city_id', $city->id));
            })
            ->withCount(['serviceProviderOrders as completed_orders_count' => fn($q) => $q->where('status', Order::COMPLETED_STATUS)])
            ->withSum(
                ['serviceProviderOrders as revenue' => fn($q) => $q->where('status', Order::COMPLETED_STATUS)],
                'subtotal'
            )
            ->with('categories:id,name')
            ->orderByDesc('completed_orders_count')
            ->take(5)
            ->get(['id', 'name', 'phone', 'entity_type', 'status']);

        // ── Top Services in City ─────────────────────────────
        $topServices = Order::select('service_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(subtotal) as total_revenue'))
            ->where($cityOrderScope)
            ->groupBy('service_id')
            ->orderByDesc('orders_count')
            ->take(5)
            ->with('service:id,name,category_id', 'service.category:id,name')
            ->get();

        // ── Categories in City ───────────────────────────────
        $categories = $city->categories()->get(['categories.id', 'categories.name']);

        return view('dashboard.cities.show', compact(
            'city',

            // Summary
            'totalServiceProviders',
            'totalUsers',
            'pendingProviders',
            'activeProviders',
            'inactiveProviders',

            // Entity types
            'individualProviders',
            'institutionProviders',
            'companyProviders',

            // Orders
            'totalOrders',
            'completedOrders',
            'newOrders',
            'onTheWayOrders',
            'arrivedOrders',
            'startedOrders',

            // Revenue
            'revenueToday',
            'revenueWeek',
            'revenueMonth',
            'revenueTotal',
            'avgOrderValue',

            // Tables
            'topProviders',
            'topServices',

            // Categories
            'categories',
        ));
    }
}


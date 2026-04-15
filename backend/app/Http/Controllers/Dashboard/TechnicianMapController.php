<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TechnicianMapController extends Controller
{
    /**
     * Display the technician tracking map page.
     */
    public function index()
    {
        Gate::authorize('view users');

        $cities = City::orderBy('item_order')->get(['id', 'name']);

        // Get stats
        $serviceProviders = User::isServiceProvider()
            ->active()
            ->hasLocation()
            ->get(['id', 'last_seen_at']);

        $onlineIds = $serviceProviders->filter(fn($u) => $u->last_seen_at && $u->last_seen_at->gte(now()->subMinutes(5)))->pluck('id');

        $busyIds = Order::whereIn('service_provider_id', $onlineIds)
            ->whereNot('status', Order::COMPLETED_STATUS)
            ->pluck('service_provider_id')
            ->unique();

        $stats = [
            'online_available' => $onlineIds->diff($busyIds)->count(),
            'online_busy' => $busyIds->count(),
            'offline' => $serviceProviders->count() - $onlineIds->count(),
            'total' => $serviceProviders->count(),
        ];

        $googleMapsApiKey = config('services.google_maps.key');
        $categories = Category::isParent()->orderBy('item_order')->get(['id', 'name']);
        return view('dashboard.technician-map.index', compact('cities', 'categories', 'stats', 'googleMapsApiKey'));
    }

    /**
     * Return technician location data as JSON (for AJAX polling).
     */
    public function api(Request $request)
    {
        Gate::authorize('view users');

        $query = User::isServiceProvider()
            ->active()
            ->hasLocation()
            ->select(['id', 'name', 'phone', 'entity_type', 'latitude', 'longitude', 'city_id', 'last_seen_at'])
            ->with(['city:id,name', 'categories:id,name']);

        // Filter by city using Geo-Spatial distance (50km radius)
        if ($request->filled('city_id')) {
            $city = City::find($request->city_id);
            if ($city && $city->latitude && $city->longitude) {
                // Haversine formula to find technicians within 50km
                $radius = 50; 
                $haversine = "(6371 * acos(cos(radians(?)) 
                                 * cos(radians(latitude)) 
                                 * cos(radians(longitude) - radians(?)) 
                                 + sin(radians(?)) 
                                 * sin(radians(latitude))))";
                                 
                $query->whereNotNull('latitude')
                      ->whereNotNull('longitude')
                      ->whereRaw("{$haversine} <= ?", [$city->latitude, $city->longitude, $city->latitude, $radius]);
            } else {
                // Fallback to strict city_id if city has no coordinates yet
                $query->where('city_id', $request->city_id);
            }
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'online') {
                $query->isOnline();
            } elseif ($request->status === 'offline') {
                $query->where(function ($q) {
                    $q->whereNull('last_seen_at')
                      ->orWhere('last_seen_at', '<', now()->subMinutes(5));
                });
            }
        }

        $technicians = $query->get();

        // Get busy technician IDs (have active orders)
        $onlineIds = $technicians->filter(fn($u) => $u->last_seen_at && $u->last_seen_at->gte(now()->subMinutes(5)))->pluck('id');

        $busyIds = Order::whereIn('service_provider_id', $onlineIds)
            ->whereNot('status', Order::COMPLETED_STATUS)
            ->pluck('service_provider_id')
            ->unique()
            ->toArray();

        $data = $technicians->map(function ($tech) use ($busyIds) {
            $isOnline = $tech->last_seen_at && $tech->last_seen_at->gte(now()->subMinutes(5));

            if ($isOnline && in_array($tech->id, $busyIds)) {
                $status = 'online_busy';
            } elseif ($isOnline) {
                $status = 'online_available';
            } else {
                $status = 'offline';
            }

            return [
                'id' => $tech->id,
                'name' => $tech->name,
                'phone' => $tech->phone,
                'latitude' => (float) $tech->latitude,
                'longitude' => (float) $tech->longitude,
                'city' => $tech->city?->name ?? '-',
                'entity_type' => __('ui.' . $tech->entity_type),
                'status' => $status,
                'last_seen_at' => $tech->last_seen_at
                    ? $tech->last_seen_at->diffForHumans()
                    : __('ui.never'),
                'avatar' => $tech->getAvatarUrl('sm'),
                'categories' => $tech->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray(),
            ];
        });

        // Calculate stats
        $stats = [
            'online_available' => $data->where('status', 'online_available')->count(),
            'online_busy' => $data->where('status', 'online_busy')->count(),
            'offline' => $data->where('status', 'offline')->count(),
            'total' => $data->count(),
        ];

        // ── Pending Orders ──
        $ordersQuery = Order::isNew()
            ->with(['category:id,name', 'customer:id,name'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('city_id') && isset($city) && $city->latitude && $city->longitude && isset($haversine) && isset($radius)) {
            $ordersQuery->whereRaw("{$haversine} <= ?", [$city->latitude, $city->longitude, $city->latitude, $radius]);
        }

        if ($request->filled('category_id')) {
            $ordersQuery->where('category_id', $request->category_id);
        }

        $pendingOrders = $ordersQuery->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'customer_name' => $order->customer->name ?? '-',
                'category_name' => $order->category->name ?? '-',
                'latitude' => (float) $order->latitude,
                'longitude' => (float) $order->longitude,
                'created_at' => $order->created_at->diffForHumans(),
                'status' => 'pending_order',
            ];
        });

        return response()->json([
            'technicians' => $data->values(),
            'pending_orders' => $pendingOrders,
            'stats' => $stats,
        ]);
    }

    /**
     * Return city insights for the side panel.
     */
    public function cityInsights(Request $request)
    {
        Gate::authorize('view users');

        $cities = City::withCount([
            'users as total_providers' => function ($query) {
                $query->where('type', User::SERVICE_PROVIDER_ACCOUNT_TYPE)->active();
            }
        ])->with(['categories' => function ($query) {
            $query->whereNull('categories.category_id');
        }])->get();

        $staleThreshold = now()->subHour();

        $insights = $cities->map(function ($city) use ($staleThreshold) {
            
            // ── Live Geo-Spatial Providers Fetching (Phase 2) ──
            $providers = collect();
            $ordersLast7d = collect();
            
            if ($city->latitude && $city->longitude) {
                $radius = 50; // 50 km coverage radius
                $haversine = "(6371 * acos(cos(radians(?)) 
                                 * cos(radians(latitude)) 
                                 * cos(radians(longitude) - radians(?)) 
                                 + sin(radians(?)) 
                                 * sin(radians(latitude))))";

                $providers = User::isServiceProvider()
                    ->active()
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereRaw("{$haversine} <= ?", [$city->latitude, $city->longitude, $city->latitude, $radius])
                    ->with('categories:id')
                    ->get(['id']);
                    
                // Fetch basic order fields for memory aggregation without doing queries for each category
                $ordersLast7d = Order::whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereRaw("{$haversine} <= ?", [$city->latitude, $city->longitude, $city->latitude, $radius])
                    ->where('created_at', '>=', now()->subDays(7))
                    ->get(['id', 'category_id', 'status', 'service_provider_id', 'created_at']);
                    
            } else {
                // Fallback for cities without coordinates
                $providers = User::isServiceProvider()
                    ->active()
                    ->where('city_id', $city->id)
                    ->with('categories:id')
                    ->get(['id']);
            }
            
            $totalOrders7d = $ordersLast7d->count();

            // Active categories in city (parents only, eagerly loaded)
            $activeCategories = $city->categories;
            $categoriesTotal = $activeCategories->count();

            $categoriesCoveredCount = 0;
            $uncoveredCategories = [];
            $categoriesBreakdown = [];

            foreach ($activeCategories as $category) {
                // Calculate providers count in memory instead of executing N queries
                $categoryProviders = $providers->filter(function($p) use ($category) {
                    return $p->categories->contains('id', $category->id);
                })->count();

                if ($categoryProviders > 0) {
                    $categoriesCoveredCount++;
                    $status = $categoryProviders > 2 ? 'good' : 'warning';
                } else {
                    $status = 'critical';
                    $uncoveredCategories[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                }
                
                // Calculate stale orders count in memory instead of executing N queries
                $staleOrdersCount = $ordersLast7d->filter(function($o) use ($category, $staleThreshold) {
                    return $o->category_id == $category->id && 
                           $o->status == Order::NEW_STATUS && 
                           is_null($o->service_provider_id) && 
                           $o->created_at < $staleThreshold;
                })->count();

                $categoriesBreakdown[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'providers_count' => $categoryProviders,
                    'stale_orders_count' => $staleOrdersCount,
                    'status' => $status,
                ];
            }

            $coveragePercentage = $categoriesTotal > 0 ? round(($categoriesCoveredCount / $categoriesTotal) * 100) : 0;
            
            $cityStatus = 'good';
            if ($categoriesTotal > 0 && $coveragePercentage == 0) {
                $cityStatus = 'critical';
            } elseif ($categoriesTotal > 0 && $coveragePercentage < 100) {
                $cityStatus = 'warning';
            }

            $demandSupplyRatio = $providers->count() > 0 ? round($totalOrders7d / $providers->count(), 1) : $totalOrders7d;

            return [
                'id' => $city->id,
                'name' => $city->name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'total_providers' => $providers->count(),
                'total_orders_7d' => $totalOrders7d,
                'demand_supply_ratio' => $demandSupplyRatio,
                'categories_covered' => $categoriesCoveredCount,
                'categories_total' => $categoriesTotal,
                'coverage_percentage' => $coveragePercentage,
                'coverage_status' => $cityStatus,
                'categories_breakdown' => $categoriesBreakdown,
                'uncovered_categories' => $uncoveredCategories,
            ];
        });

        // Generate actionable alerts based on insights
        $alerts = [];
        foreach ($insights as $cityInsight) {
            foreach ($cityInsight['uncovered_categories'] as $uncovered) {
                $alerts[] = [
                    'type' => 'zero_coverage',
                    'city_id' => $cityInsight['id'],
                    'city_name' => $cityInsight['name'],
                    'category_id' => $uncovered['id'],
                    'category_name' => $uncovered['name'],
                    'severity' => 'critical',
                ];
            }
        }

        return response()->json([
            'cities_overview' => $insights->values(),
            'alerts' => collect($alerts)->sortByDesc('severity')->values(),
        ]);
    }

    /**
     * Return orders coordinates for the heatmap layer.
     */
    public function heatmapData(Request $request)
    {
        Gate::authorize('view users');

        // Get orders from last 7 days with valid location
        $orders = Order::where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['latitude', 'longitude', 'status']);
            
        $heatmapArray = $orders->map(function ($order) {
            return [
                'lat' => (float) $order->latitude,
                'lng' => (float) $order->longitude,
                'weight' => $order->status === Order::COMPLETED_STATUS ? 2 : 1,
            ];
        });

        return response()->json($heatmapArray);
    }
}

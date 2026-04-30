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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
        $categories = Category::isParent()
            ->withCount(['serviceProviders as technicians_count' => function ($q) {
                $q->where('type', User::SERVICE_PROVIDER_ACCOUNT_TYPE);
            }])
            ->orderBy('item_order')
            ->get(['id', 'name']);
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
            if (in_array($request->status, ['online', 'online_available', 'online_busy'])) {
                $query->isOnline();
            } elseif ($request->status === 'offline') {
                $query->where(function ($q) {
                    $q->whereNull('last_seen_at')
                      ->orWhere('last_seen_at', '<', now()->subMinutes(5));
                });
            }
        }

        // If filtering specifically for online_available or online_busy,
        // we narrow down after the main query (since busy status depends on orders)
        $statusFilterNarrow = $request->status;

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

        // Narrow down by sub-status if specifically requested
        if (isset($statusFilterNarrow) && in_array($statusFilterNarrow, ['online_available', 'online_busy'])) {
            $data = $data->where('status', $statusFilterNarrow);
        }

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
            if ($categoriesTotal == 0 || ($providers->count() == 0 && $categoriesTotal > 0)) {
                $cityStatus = 'critical';
            } elseif ($categoriesTotal > 0 && $coveragePercentage == 0) {
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

    /**
     * Export comprehensive Excel report from map data.
     */
    public function exportExcel()
    {
        Gate::authorize('view users');
        
        // Allow enough time for complex location-based calculations across all cities
        set_time_limit(300);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Segoe UI')->setSize(11);

        // ── Theme Colors ──
        $headerBg    = '1F4E79';  // Dark blue
        $headerFont  = 'FFFFFF';  // White
        $goodBg      = 'C6EFCE';  // Green
        $goodFont    = '006100';
        $warningBg   = 'FFEB9C';  // Yellow
        $warningFont = '9C6500';
        $criticalBg  = 'FFC7CE';  // Red
        $criticalFont= '9C0006';
        $stripeBg    = 'F2F7FB';  // Light blue
        $summaryBg   = 'D6E4F0';  // Summary row bg
        $accentBg    = 'E2EFDA';  // Accent green

        // ═══════════════════════════════════════════════
        // Gather all data
        // ═══════════════════════════════════════════════
        $cities = City::orderBy('item_order')->get();
        $categories = Category::isParent()->orderBy('item_order')->get(['id', 'name']);
        $staleThreshold = now()->subHour();

        $cityData = [];
        $categoryBreakdownRows = [];
        $alertRows = [];

        foreach ($cities as $city) {
            // Providers
            $providersQuery = User::isServiceProvider()->active();
            $customersQuery = User::isUser();

            if ($city->latitude && $city->longitude) {
                $radius = 50;
                $haversine = "(6371 * acos(cos(radians({$city->latitude}))
                                 * cos(radians(latitude))
                                 * cos(radians(longitude) - radians({$city->longitude}))
                                 + sin(radians({$city->latitude}))
                                 * sin(radians(latitude))))";

                $providers = $providersQuery
                    ->whereNotNull('latitude')->whereNotNull('longitude')
                    ->whereRaw("{$haversine} <= ?", [$radius])
                    ->with('categories:id')
                    ->get(['id', 'last_seen_at']);

                $customersCount = $customersQuery
                    ->where('city_id', $city->id)
                    ->count();

                $ordersLast7d = Order::whereNotNull('latitude')->whereNotNull('longitude')
                    ->whereRaw("{$haversine} <= ?", [$radius])
                    ->where('created_at', '>=', now()->subDays(7))
                    ->get(['id', 'category_id', 'status', 'service_provider_id', 'created_at']);
            } else {
                $providers = $providersQuery
                    ->where('city_id', $city->id)
                    ->with('categories:id')
                    ->get(['id', 'last_seen_at']);

                $customersCount = $customersQuery
                    ->where('city_id', $city->id)
                    ->count();

                $ordersLast7d = collect();
            }

            $onlineProviders = $providers->filter(fn($u) => $u->last_seen_at && $u->last_seen_at->gte(now()->subMinutes(5)));
            $busyIds = $onlineProviders->count() > 0
                ? Order::whereIn('service_provider_id', $onlineProviders->pluck('id'))
                    ->whereNot('status', Order::COMPLETED_STATUS)
                    ->pluck('service_provider_id')->unique()
                : collect();

            $completedOrders = $ordersLast7d->where('status', Order::COMPLETED_STATUS)->count();
            $pendingOrders = $ordersLast7d->filter(fn($o) => $o->status == Order::NEW_STATUS && is_null($o->service_provider_id))->count();

            // Active categories
            $activeCategories = $city->categories()->whereNull('categories.category_id')->get();
            $categoriesCovered = 0;
            $categoriesTotal = $activeCategories->count();

            foreach ($activeCategories as $cat) {
                $catProviders = $providers->filter(fn($p) => $p->categories->contains('id', $cat->id))->count();

                if ($catProviders > 0) $categoriesCovered++;

                $status = $catProviders > 2 ? 'good' : ($catProviders > 0 ? 'warning' : 'critical');

                $staleOrdersCount = $ordersLast7d->filter(fn($o) =>
                    $o->category_id == $cat->id &&
                    $o->status == Order::NEW_STATUS &&
                    is_null($o->service_provider_id) &&
                    $o->created_at < $staleThreshold
                )->count();

                $categoryBreakdownRows[] = [
                    'city' => $city->name,
                    'category' => $cat->name,
                    'providers' => $catProviders,
                    'stale_orders' => $staleOrdersCount,
                    'status' => $status,
                ];

                if ($catProviders === 0) {
                    $alertRows[] = [
                        'city' => $city->name,
                        'category' => $cat->name,
                        'severity' => 'حرج',
                        'action' => 'مطلوب إضافة فنيين',
                    ];
                }
            }

            $coveragePct = $categoriesTotal > 0 ? round(($categoriesCovered / $categoriesTotal) * 100) : 0;

            $providersCount = $providers->count();
            if ($providersCount < 15) {
                $cityStatus = 'critical';
            } elseif ($providersCount < 30) {
                $cityStatus = 'warning';
            } elseif ($providersCount <= 50) {
                $cityStatus = 'good';
            } else {
                $cityStatus = 'excellent';
            }

            $cityData[] = [
                'name' => $city->name,
                'total_providers' => $providers->count(),
                'online_providers' => $onlineProviders->count(),
                'busy_providers' => $busyIds->count(),
                'customers' => $customersCount,
                'orders_7d' => $ordersLast7d->count(),
                'completed' => $completedOrders,
                'pending' => $pendingOrders,
                'coverage' => $coveragePct . '%',
                'status' => $cityStatus,
                'demand_ratio' => $providers->count() > 0
                    ? round($ordersLast7d->count() / $providers->count(), 1)
                    : ($ordersLast7d->count() > 0 ? '∞' : '0'),
                'category_providers' => collect($categories)->mapWithKeys(function ($cat) use ($providers) {
                    return [$cat->id => $providers->filter(fn($p) => $p->categories->contains('id', $cat->id))->count()];
                })->toArray(),
            ];
        }

        // ═══════════════════════════════════════════════
        // Sheet 1: ملخص المدن
        // ═══════════════════════════════════════════════
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('ملخص المدن');
        $sheet1->setRightToLeft(true);

        // Summary header row
        $totalProviders = array_sum(array_column($cityData, 'total_providers'));
        $totalCustomers = array_sum(array_column($cityData, 'customers'));
        $totalOrders = array_sum(array_column($cityData, 'orders_7d'));
        $totalCompleted = array_sum(array_column($cityData, 'completed'));
        $totalPending = array_sum(array_column($cityData, 'pending'));

        // KPI Summary row
        $sheet1->setCellValue('A1', 'تقرير خريطة الفنيين - ' . now()->format('Y/m/d H:i'));
        $sheet1->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $headerBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(1)->setRowHeight(35);

        // KPI Row
        $kpis = [
            ['إجمالي الفنيين', $totalProviders],
            ['إجمالي العملاء', $totalCustomers],
            ['الطلبات (7 أيام)', $totalOrders],
            ['الطلبات المكتملة', $totalCompleted],
            ['الطلبات المعلقة', $totalPending],
        ];
        $col = 'A';
        foreach ($kpis as $kpi) {
            $sheet1->setCellValue("{$col}2", $kpi[0]);
            $sheet1->setCellValue("{$col}3", $kpi[1]);
            $sheet1->getStyle("{$col}2")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $summaryBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet1->getStyle("{$col}3")->applyFromArray([
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $headerBg]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $summaryBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $col++;
        }
        $sheet1->getRowDimension(2)->setRowHeight(22);
        $sheet1->getRowDimension(3)->setRowHeight(30);

        // Build headers: fixed + dynamic category columns
        $fixedHeaders = ['المدينة', 'عدد الفنيين', 'عدد العملاء', 'الطلبات (7 أيام)', 'مكتملة', 'معلقة', 'نسبة التغطية', 'الحالة'];
        $categoryHeaders = $categories->pluck('name')->toArray();
        $headers = array_merge($fixedHeaders, $categoryHeaders);
        $fixedCount = count($fixedHeaders);
        $totalCols = count($headers);
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);

        // Title merge
        $sheet1->mergeCells("A1:{$lastCol}1");

        $col = 'A';
        $headerRow = 5;
        foreach ($headers as $header) {
            $sheet1->setCellValue("{$col}{$headerRow}", $header);
            $sheet1->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        // Style fixed headers blue
        $fixedLastCol = Coordinate::stringFromColumnIndex($fixedCount);
        $this->applyHeaderStyle($sheet1, "A{$headerRow}:{$fixedLastCol}{$headerRow}", $headerBg, $headerFont);
        // Style category headers with different color (purple)
        $catStartCol = Coordinate::stringFromColumnIndex($fixedCount + 1);
        $this->applyHeaderStyle($sheet1, "{$catStartCol}{$headerRow}:{$lastCol}{$headerRow}", '7B2D8E', $headerFont);
        $sheet1->getRowDimension($headerRow)->setRowHeight(32);
        $sheet1->freezePane('A' . ($headerRow + 1));

        // Data rows
        $row = $headerRow + 1;
        foreach ($cityData as $i => $data) {
            $sheet1->setCellValue("A{$row}", $data['name']);
            $sheet1->setCellValue("B{$row}", $data['total_providers']);
            $sheet1->setCellValue("C{$row}", $data['customers']);
            $sheet1->setCellValue("D{$row}", $data['orders_7d']);
            $sheet1->setCellValue("E{$row}", $data['completed']);
            $sheet1->setCellValue("F{$row}", $data['pending']);
            $sheet1->setCellValue("G{$row}", $data['coverage']);

            $statusLabel = match($data['status']) {
                'excellent' => 'ممتاز 🌟',
                'good' => 'جيد ✅',
                'warning' => 'تحذير ⚠️',
                'critical' => 'حرج 🔴',
                default => $data['status'],
            };
            $sheet1->setCellValue("H{$row}", $statusLabel);

            // Status cell coloring
            $this->applyStatusColor($sheet1, "H{$row}", $data['status'], $goodBg, $goodFont, $warningBg, $warningFont, $criticalBg, $criticalFont);

            // Pending orders coloring (red if > 0)
            if ($data['pending'] > 0) {
                $sheet1->getStyle("F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $criticalFont]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $criticalBg]],
                ]);
            }

            // Dynamic category columns
            $catColIdx = $fixedCount + 1;
            foreach ($categories as $cat) {
                $catColLetter = Coordinate::stringFromColumnIndex($catColIdx);
                $catCount = $data['category_providers'][$cat->id] ?? 0;
                $sheet1->setCellValue("{$catColLetter}{$row}", $catCount);

                // Color code based on count
                if ($catCount < 10) {
                    $sheet1->getStyle("{$catColLetter}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $criticalFont]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $criticalBg]],
                    ]);
                } elseif ($catCount < 20) {
                    $sheet1->getStyle("{$catColLetter}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $warningFont]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $warningBg]],
                    ]);
                } else {
                    $sheet1->getStyle("{$catColLetter}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => $goodFont]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $goodBg]],
                    ]);
                }
                $catColIdx++;
            }

            // Zebra striping (fixed columns only, categories have their own colors)
            if ($i % 2 === 1) {
                $sheet1->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $stripeBg]],
                ]);
            }

            $this->applyRowStyle($sheet1, "A{$row}:{$lastCol}{$row}");
            $sheet1->getRowDimension($row)->setRowHeight(26);
            $row++;
        }

        // Total summary row
        $sheet1->setCellValue("A{$row}", 'الإجمالي');
        $sheet1->setCellValue("B{$row}", $totalProviders);
        $sheet1->setCellValue("C{$row}", $totalCustomers);
        $sheet1->setCellValue("D{$row}", $totalOrders);
        $sheet1->setCellValue("E{$row}", $totalCompleted);
        $sheet1->setCellValue("F{$row}", $totalPending);
        // Category totals
        $catColIdx = $fixedCount + 1;
        foreach ($categories as $cat) {
            $catColLetter = Coordinate::stringFromColumnIndex($catColIdx);
            $catTotal = array_sum(array_column(array_column($cityData, 'category_providers'), $cat->id));
            $sheet1->setCellValue("{$catColLetter}{$row}", $catTotal);
            $catColIdx++;
        }
        $sheet1->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension($row)->setRowHeight(30);

        // Add border to entire data range
        $this->applyTableBorder($sheet1, "A{$headerRow}:{$lastCol}{$row}");


        // ═══════════════════════════════════════════════
        // Sheet 2: الأقسام حسب المدينة
        // ═══════════════════════════════════════════════
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('الأقسام حسب المدينة');
        $sheet2->setRightToLeft(true);

        $headers2 = ['المدينة', 'القسم', 'عدد الفنيين', 'طلبات معلقة', 'الحالة'];
        $col = 'A';
        foreach ($headers2 as $header) {
            $sheet2->setCellValue("{$col}1", $header);
            $sheet2->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $this->applyHeaderStyle($sheet2, 'A1:E1', $headerBg, $headerFont);
        $sheet2->getRowDimension(1)->setRowHeight(32);
        $sheet2->freezePane('A2');

        $row = 2;
        $prevCity = '';
        foreach ($categoryBreakdownRows as $i => $data) {
            $displayCity = $data['city'] !== $prevCity ? $data['city'] : '';
            $prevCity = $data['city'];

            $sheet2->setCellValue("A{$row}", $displayCity);
            $sheet2->setCellValue("B{$row}", $data['category']);
            $sheet2->setCellValue("C{$row}", $data['providers']);
            $sheet2->setCellValue("D{$row}", $data['stale_orders']);

            $statusLabel = match($data['status']) {
                'good' => 'جيد ✅',
                'warning' => 'تحذير ⚠️',
                'critical' => 'حرج 🔴',
                default => $data['status'],
            };
            $sheet2->setCellValue("E{$row}", $statusLabel);
            $this->applyStatusColor($sheet2, "E{$row}", $data['status'], $goodBg, $goodFont, $warningBg, $warningFont, $criticalBg, $criticalFont);

            if ($data['stale_orders'] > 0) {
                $sheet2->getStyle("D{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $criticalFont]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $criticalBg]],
                ]);
            }

            // City group separator
            if ($displayCity !== '') {
                $sheet2->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            }

            $this->applyRowStyle($sheet2, "A{$row}:E{$row}");
            $sheet2->getRowDimension($row)->setRowHeight(24);
            $row++;
        }
        $this->applyTableBorder($sheet2, 'A1:E' . ($row - 1));


        // ═══════════════════════════════════════════════
        // Sheet 3: قائمة الفنيين
        // ═══════════════════════════════════════════════
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('قائمة الفنيين');
        $sheet3->setRightToLeft(true);

        $technicians = User::isServiceProvider()
            ->active()
            ->select(['id', 'name', 'phone', 'entity_type', 'city_id', 'last_seen_at'])
            ->with(['city:id,name', 'categories:id,name'])
            ->withCount([
                'serviceProviderOrders as completed_orders' => fn($q) => $q->completed(),
            ])
            ->withSum([
                'serviceProviderOrders as total_earnings' => fn($q) => $q->completed(),
            ], 'subtotal')
            ->orderBy('name')
            ->get();

        $onlineIds = $technicians->filter(fn($u) => $u->last_seen_at && $u->last_seen_at->gte(now()->subMinutes(5)))->pluck('id');
        $busyIds = Order::whereIn('service_provider_id', $onlineIds)
            ->whereNot('status', Order::COMPLETED_STATUS)
            ->pluck('service_provider_id')->unique()->toArray();

        $headers3 = ['#', 'الاسم', 'الهاتف', 'النوع', 'المدينة', 'الأقسام', 'الحالة', 'آخر ظهور', 'الطلبات المكتملة', 'إجمالي الأرباح'];
        $col = 'A';
        foreach ($headers3 as $header) {
            $sheet3->setCellValue("{$col}1", $header);
            $sheet3->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $this->applyHeaderStyle($sheet3, 'A1:J1', $headerBg, $headerFont);
        $sheet3->getRowDimension(1)->setRowHeight(32);
        $sheet3->freezePane('A2');

        $row = 2;
        foreach ($technicians as $i => $tech) {
            $isOnline = $tech->last_seen_at && $tech->last_seen_at->gte(now()->subMinutes(5));
            $isBusy = in_array($tech->id, $busyIds);

            $status = $isOnline ? ($isBusy ? 'online_busy' : 'online_available') : 'offline';
            $statusLabel = match($status) {
                'online_available' => 'متصل متاح 🟢',
                'online_busy' => 'متصل مشغول 🟠',
                'offline' => 'غير متصل ⚫',
            };

            $entityLabel = match($tech->entity_type) {
                'individual' => 'فرد',
                'institution' => 'مؤسسة',
                'company' => 'شركة',
                default => $tech->entity_type ?? '-',
            };

            $catNames = $tech->categories->pluck('name')->join('، ');

            $sheet3->setCellValue("A{$row}", $i + 1);
            $sheet3->setCellValue("B{$row}", $tech->name);
            $sheet3->setCellValue("C{$row}", $tech->phone);
            $sheet3->getStyle("C{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $sheet3->setCellValue("D{$row}", $entityLabel);
            $sheet3->setCellValue("E{$row}", $tech->city?->name ?? '-');
            $sheet3->setCellValue("F{$row}", $catNames ?: '-');
            $sheet3->setCellValue("G{$row}", $statusLabel);
            $sheet3->setCellValue("H{$row}", $tech->last_seen_at ? $tech->last_seen_at->diffForHumans() : 'لم يظهر');
            $sheet3->setCellValue("I{$row}", $tech->completed_orders ?? 0);
            $sheet3->setCellValue("J{$row}", number_format($tech->total_earnings ?? 0, 2));

            // Status coloring
            $mappedStatus = match($status) {
                'online_available' => 'good',
                'online_busy' => 'warning',
                'offline' => 'critical',
            };
            $this->applyStatusColor($sheet3, "G{$row}", $mappedStatus, $goodBg, $goodFont, $warningBg, $warningFont, $criticalBg, $criticalFont);

            if ($i % 2 === 1) {
                $sheet3->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $stripeBg]],
                ]);
            }

            $this->applyRowStyle($sheet3, "A{$row}:J{$row}");
            $sheet3->getRowDimension($row)->setRowHeight(24);
            $row++;
        }

        // Summary row
        $sheet3->setCellValue("A{$row}", 'الإجمالي: ' . $technicians->count() . ' فني');
        $sheet3->mergeCells("A{$row}:F{$row}");
        $sheet3->setCellValue("I{$row}", $technicians->sum('completed_orders'));
        $sheet3->setCellValue("J{$row}", number_format($technicians->sum('total_earnings') ?? 0, 2));
        $sheet3->getStyle("A{$row}:J{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet3->getRowDimension($row)->setRowHeight(28);
        $this->applyTableBorder($sheet3, 'A1:J' . $row);


        // ═══════════════════════════════════════════════
        // Sheet 4: تنبيهات النقص
        // ═══════════════════════════════════════════════
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('تنبيهات النقص');
        $sheet4->setRightToLeft(true);

        $headers4 = ['#', 'المدينة', 'القسم', 'مستوى الخطورة', 'الإجراء المطلوب'];
        $col = 'A';
        foreach ($headers4 as $header) {
            $sheet4->setCellValue("{$col}1", $header);
            $sheet4->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        // Red header for alerts
        $this->applyHeaderStyle($sheet4, 'A1:E1', '9C0006', $headerFont);
        $sheet4->getRowDimension(1)->setRowHeight(32);
        $sheet4->freezePane('A2');

        if (count($alertRows) > 0) {
            $row = 2;
            foreach ($alertRows as $i => $alert) {
                $sheet4->setCellValue("A{$row}", $i + 1);
                $sheet4->setCellValue("B{$row}", $alert['city']);
                $sheet4->setCellValue("C{$row}", $alert['category']);
                $sheet4->setCellValue("D{$row}", $alert['severity']);
                $sheet4->setCellValue("E{$row}", $alert['action']);

                $sheet4->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $i % 2 === 0 ? $criticalBg : 'FFF2F2']],
                    'font' => ['color' => ['rgb' => $criticalFont]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet4->getRowDimension($row)->setRowHeight(24);
                $row++;
            }
            $this->applyTableBorder($sheet4, 'A1:E' . ($row - 1));
        } else {
            $sheet4->setCellValue('A2', 'لا توجد تنبيهات - جميع المدن مغطاة ✅');
            $sheet4->mergeCells('A2:E2');
            $sheet4->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $goodFont]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $goodBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet4->getRowDimension(2)->setRowHeight(40);
        }

        // ═══════════════════════════════════════════════
        // Set active sheet to first
        // ═══════════════════════════════════════════════
        $spreadsheet->setActiveSheetIndex(0);

        // ═══════════════════════════════════════════════
        // Export
        // ═══════════════════════════════════════════════
        $filename = 'technician-map-report-' . now()->format('Y-m-d-His') . '.xlsx';

        $excelDir = public_path('excel');
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0755, true);
        }

        // Cleanup old reports (keep last 5)
        $oldFiles = glob($excelDir . '/technician-map-report-*.xlsx');
        if (count($oldFiles) > 5) {
            usort($oldFiles, fn($a, $b) => filemtime($a) - filemtime($b));
            $toDelete = array_slice($oldFiles, 0, count($oldFiles) - 5);
            foreach ($toDelete as $old) {
                @unlink($old);
            }
        }

        $filePath = $excelDir . '/' . $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return redirect(asset("excel/{$filename}") . '?t=' . now()->getTimestamp());
    }

    // ── Helper: Apply header style ──
    private function applyHeaderStyle($sheet, string $range, string $bgColor, string $fontColor): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => $fontColor],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $bgColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    // ── Helper: Apply status color ──
    private function applyStatusColor($sheet, string $cell, string $status, string $goodBg, string $goodFont, string $warningBg, string $warningFont, string $criticalBg, string $criticalFont): void
    {
        [$bg, $font] = match($status) {
            'excellent' => ['00B050', 'FFFFFF'],
            'good' => [$goodBg, $goodFont],
            'warning' => [$warningBg, $warningFont],
            'critical' => [$criticalBg, $criticalFont],
            default => ['FFFFFF', '000000'],
        };

        $sheet->getStyle($cell)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $font]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    // ── Helper: Apply row style ──
    private function applyRowStyle($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    // ── Helper: Apply table border ──
    private function applyTableBorder($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
    }
}

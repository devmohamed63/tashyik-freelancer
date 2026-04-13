<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
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
        return view('dashboard.technician-map.index', compact('cities', 'stats', 'googleMapsApiKey'));
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
            ->with('city:id,name');

        // Filter by city
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
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
            ];
        });

        // Calculate stats
        $stats = [
            'online_available' => $data->where('status', 'online_available')->count(),
            'online_busy' => $data->where('status', 'online_busy')->count(),
            'offline' => $data->where('status', 'offline')->count(),
            'total' => $data->count(),
        ];

        return response()->json([
            'technicians' => $data->values(),
            'stats' => $stats,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Models\Order;
use App\Events\OrderStarted;
use App\Events\OrderAccepted;
use App\Events\OrderCompleted;
use App\Events\ServiceProviderArrived;
use App\Http\Resources\ServiceProvider\OrderResource;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrderController extends ApiController
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:new,completed']
        ]);

        $serviceProvider = Auth::user();

        $serviceProvider->authorizeLocation();

        $cacheKey = "ignored-orders-$serviceProvider->id";

        $ignoredOrderIds = Cache::get($cacheKey, []);

        $query = Order::select(['id', 'service_id', 'latitude', 'longitude', 'subtotal', 'created_at'])
            ->with(['service:id,name'])
            ->whereNotIn('id', $ignoredOrderIds)
            ->orderByDesc('id');

        switch ($request->status) {
            case 'new':
                $categoryIds = $serviceProvider->categories()->pluck('id')->toArray();
                $query->isNew()->withinMaxDistance()->whereIn('category_id', $categoryIds);
                break;

            case 'completed':
                $query->completed()->where('service_provider_id', $serviceProvider->id);
                break;
        }

        $orders = $query->paginate($this->paginationLimit);

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $mapUrl = "https://maps.google.com/maps?q={$order->latitude},{$order->longitude}";

        $subtotal = $order->visit_cost ? 0 : $order->subtotal;

        return [
            'id' => $order->id,
            'service' => [
                'id' => $order->service?->id,
                'name' => $order->service?->name,
            ],
            'customer' => [
                'id' => $order->customer?->id,
                'name' => $order->customer?->name,
                'phone' => $order->customer?->phone,
            ],
            'location' => [
                'map_url' => $mapUrl,
                'address' => $order->address?->address,
                'landmark' => $order->address?->landmark,
                'building_number' => $order->address?->building_number,
                'floor_number' => $order->address?->floor_number,
                'apartment_number' => $order->address?->apartment_number,
            ],
            'status' => $order->status,
            'description' => $order->description,
            'quantity' => $order->quantity,
            'price' => number_format($subtotal, config('app.decimal_places')),
            'visit_cost' => $order->visit_cost,
            'currency' => __('ui.currency'),
            'created_at' => $order->created_at->isoFormat(config('app.time_format')),
        ];
    }

    public function update(Order $order, Request $request)
    {
        Gate::authorize('updateStatus', [$order, $request->status]);

        $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        $order->service_provider_id = Auth::user()->id;
        $order->service_provider_notes = $request->notes;
        $order->status = $request->status;
        $order->save();

        switch ($request->status) {
            case Order::SERVICE_PROVIDER_ON_THE_WAY:
                OrderAccepted::dispatch($order);
                break;

            case Order::SERVICE_PROVIDER_ARRIVED:
                ServiceProviderArrived::dispatch($order);
                break;

            case Order::STARTED_STATUS:
                OrderStarted::dispatch($order);
                break;

            case Order::COMPLETED_STATUS:
                OrderCompleted::dispatch($order);
                break;
        }

        return response('');
    }

    public function destroy(Order $order)
    {
        $serviceProvider = Auth::user();

        $cacheKey = "ignored-orders-$serviceProvider->id";

        $ignoredOrderIds = Cache::get($cacheKey, []);

        $ignoredOrderIds = array_unique([...$ignoredOrderIds, $order->id]);

        Cache::put($cacheKey, $ignoredOrderIds, now()->endOfDay());

        return response('');
    }
}

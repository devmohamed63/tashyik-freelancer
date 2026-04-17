<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Models\Order;
use App\Models\Service;
use App\Models\OrderExtra;
use App\Events\NewOrderExtra;
use App\Http\Requests\OrderExtraRequest;
use App\Http\Resources\OrderExtraResource;
use App\Utils\Http\Controllers\ApiController;
use App\Utils\Traits\HasTax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderExtraController extends ApiController
{
    use HasTax;

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

    public function store(OrderExtraRequest $request)
    {
        Gate::authorize('create', OrderExtra::class);

        $order = Order::find($request->order);

        $service = Service::find($request->service);

        $service->load('promotion');

        $price = $service->getPrice(false)['after_discount'];

        $orderExtra = new OrderExtra();
        $orderExtra->order_id = $order->id;
        $orderExtra->service_id = $request->service;
        $orderExtra->status = OrderExtra::PENDING_STATUS;
        $orderExtra->quantity = $request->quantity;
        $orderExtra->price = $price;
        $orderExtra->tax_rate = config('app.tax_rate');
        $orderExtra->tax = $this->getTaxes($price);
        $orderExtra->materials = $request->materials;
        $orderExtra->save();

        NewOrderExtra::dispatch($orderExtra);

        return response('');
    }
}

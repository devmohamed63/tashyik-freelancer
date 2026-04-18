<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\OrderExreaServiceResource;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ServiceController extends ApiController
{
    public function index(Request $request)
    {
        $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Service::query()
            ->orderBy('item_order')
            ->with([
                'promotion',
                'media',
                'highlights:id,service_id,title'
            ]);

        // Search filter
        if ($request->q) {
            $query = $query->where(function (Builder $q) use ($request) {
                $q->where('name', 'LIKE', "%$request->q%")
                    ->orWhere('description', 'LIKE', "%$request->q%")
                    ->orWhereRelation('highlights', 'title', 'LIKE', "%$request->q%");
            });
        }

        // Category filter
        if ($request->category) {
            $query = $query->where('category_id', $request->category);
        }

        $services = $query->paginate($this->paginationLimit, [
            'id',
            'slug',
            'promotion_id',
            'category_id',
            'name',
            'badge',
            'price',
            'warranty_days',
        ]);

        return ServiceResource::collection($services);
    }

    public function get_services_for_order_extra(Request $request)
    {
        $serviceProvider = Auth::user();

        $query = Service::query()
            ->with(['promotion'])
            ->orderBy('name');

        $mainCategoryIds = $serviceProvider->categories()->pluck('id')->toArray();

        // Filter services by service provider's categories
        $query = $query->whereHas('category', function (Builder $categories) use ($mainCategoryIds) {
            $categories->whereIn('category_id', $mainCategoryIds);
        });

        $services = $query->get([
            'id',
            'slug',
            'promotion_id',
            'category_id',
            'name',
            'price',
        ]);

        return OrderExreaServiceResource::collection($services);
    }

    public function show(Service $service)
    {
        $service->load(['media', 'highlights:id,service_id,title']);

        return new ServiceResource($service);
    }
}

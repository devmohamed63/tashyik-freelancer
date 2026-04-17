<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Service::class);

        return view('dashboard.services.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Service::class);

        $categories = Category::isChild()->orderBy('name')->get(['id', 'name']);

        return view('dashboard.services.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        Gate::authorize('create', Service::class);

        $service = new Service([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'warranty_days' => $request->total_warranty_days,
        ]);

        $service->category_id = $request->category;

        $service->save();

        $service->addMediaFromRequest('image')
            ->toMediaCollection('image');

        if ($request->gallery) {
            foreach ($request->gallery as $image) {
                $service->addMedia($image)
                    ->toMediaCollection('gallery');
            }
        }

        $service->highlights()->saveMany($request->highlights);

        return redirect()->back()->with(['status' => __('ui.added_successfully')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        Gate::authorize('view', $service);

        $image = $service->getImageUrl('sm');

        return view('dashboard.services.show', compact('service', 'image'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        Gate::authorize('update', $service);

        $categories = Category::isChild()->orderBy('name')->get(['id', 'name']);

        $image = $service->getImageUrl('sm');

        $totalWarrantyDays = $service->warranty_days;

        $warrantMonths = intdiv($totalWarrantyDays, 28);

        $warrantyDays = $totalWarrantyDays % 28;

        $highlights = $service->highlights->pluck('title')->toArray();
        $highlights = array_map(fn($h) => "'$h'", $highlights);
        $highlights = implode(',', $highlights);

        return view('dashboard.services.edit', compact('service', 'image', 'categories', 'warrantyDays', 'warrantMonths', 'highlights'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, Service $service)
    {
        Gate::authorize('update', $service);

        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'warranty_days' => $request->total_warranty_days,
        ]);

        $service->category_id = $request->category;

        $service->save();

        if ($request->image) {
            $service->addMediaFromRequest('image')->toMediaCollection('image');
        }

        if ($request->gallery) {
            $service->clearMediaCollection('gallery');

            foreach ($request->gallery as $image) {
                $service->addMedia($image)
                    ->toMediaCollection('gallery');
            }
        }

        $service->highlights()->delete();
        $service->highlights()->saveMany($request->highlights);

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Banner;
use Illuminate\Support\Facades\Gate;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Banner::class);

        return view('dashboard.banners.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Banner::class);

        return view('dashboard.banners.create');
    }

    /**
     * Store banner.
     */
    public function store(BannerRequest $request)
    {
        Gate::authorize('create', Banner::class);

        $banner = Banner::create($request->validated());

        $banner->addMediaFromRequest('image')->toMediaCollection('image');

        return redirect()->back()->with(['status' => __('ui.created_successfully')]);
    }

    /**
     * Edit banner form.
     */
    public function edit(Banner $banner)
    {
        Gate::authorize('update', $banner);

        $image = $banner->getImageUrl('lg');

        return view('dashboard.banners.edit', compact('banner', 'image'));
    }

    /**
     * Update banner.
     */
    public function update(Banner $banner, BannerRequest $request)
    {
        Gate::authorize('update', $banner);

        $banner->update($request->validated());

        if ($request->image) {
            $banner->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }

    /**
     * Show banner.
     */
    public function show(Banner $banner)
    {
        Gate::authorize('view', $banner);

        $image = $banner->getImageUrl('lg');

        $date = $banner->created_at->isoFormat(config('app.time_format'));

        return view('dashboard.banners.show', compact('banner', 'image', 'date'));
    }
}

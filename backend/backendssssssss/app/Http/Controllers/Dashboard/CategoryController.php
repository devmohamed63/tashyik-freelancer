<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Category::class);

        return view('dashboard.categories.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function children()
    {
        Gate::authorize('viewAny', Category::class);

        return view('dashboard.categories.children');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Category::class);

        $cities = City::orderBy('name')->get(['id', 'name']);

        $categories = Category::isParent()->orderBy('name')->get(['id', 'name']);

        return view('dashboard.categories.create', compact('cities', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Gate::authorize('create', Category::class);

        $category = new Category([
            'name' => $request->name,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ]);

        $category->category_id = $request->parent;

        $category->save();

        if (!$request->parent) {
            $category->cities()->attach($request->cities);
        }

        $category->addMediaFromRequest('image')
            ->toMediaCollection('image');

        if ($request->gallery) {
            foreach ($request->gallery as $image) {
                $category->addMedia($image)
                    ->toMediaCollection('gallery');
            }
        }

        return redirect()->back()->with(['status' => __('ui.added_successfully')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        $image = $category->getImageUrl('sm');

        $cities = $category->cities()->pluck('name')->toArray();

        return view('dashboard.categories.show', compact('category', 'image', 'cities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        Gate::authorize('update', $category);

        $cities = City::orderBy('name')->get(['id', 'name']);

        $categoryCities = $category->cities()->pluck('id')->toArray();

        $categories = Category::isParent()
            ->whereNot('id', $category->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $image = $category->getImageUrl('sm');

        return view('dashboard.categories.edit', compact('category', 'image', 'cities', 'categoryCities', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
        ]);

        $category->category_id = $request->parent;

        $category->save();

        if ($request->parent) {
            $category->cities()->detach();
        } else {
            $category->cities()->sync($request->cities);
        }

        if ($request->image) {
            $category->addMediaFromRequest('image')->toMediaCollection('image');
        }

        if ($request->gallery) {
            $category->clearMediaCollection('gallery');

            foreach ($request->gallery as $image) {
                $category->addMedia($image)
                    ->toMediaCollection('gallery');
            }
        }

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use Illuminate\Support\Facades\Gate;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Page::class);

        return view('dashboard.pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Page::class);

        return view('dashboard.pages.create');
    }

    /**
     * Store page.
     */
    public function store(PageRequest $request)
    {
        Gate::authorize('create', Page::class);

        Page::create($request->validated());

        return redirect()->back()->with(['status' => __('ui.created_successfully')]);
    }

    /**
     * Edit page form.
     */
    public function edit(Page $page)
    {
        Gate::authorize('update', $page);

        return view('dashboard.pages.edit', compact('page'));
    }

    /**
     * Update page.
     */
    public function update(Page $page, PageRequest $request)
    {
        Gate::authorize('update', $page);

        $page->update($request->validated());

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }
}

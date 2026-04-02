<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Article::class);

        return view('dashboard.articles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Article::class);

        return view('dashboard.articles.create');
    }

    /**
     * Store article.
     */
    public function store(ArticleRequest $request)
    {
        Gate::authorize('create', Article::class);

        $article = Article::create($request->validated());

        if ($request->hasFile('featured_image')) {
            $article->addMediaFromRequest('featured_image')->toMediaCollection('featured_image');
        }

        return redirect()->back()->with(['status' => __('ui.created_successfully')]);
    }

    /**
     * Edit article form.
     */
    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        $image = $article->getImageUrl('lg');

        return view('dashboard.articles.edit', compact('article', 'image'));
    }

    /**
     * Update article.
     */
    public function update(Article $article, ArticleRequest $request)
    {
        Gate::authorize('update', $article);

        $article->update($request->validated());

        if ($request->hasFile('featured_image')) {
            $article->addMediaFromRequest('featured_image')->toMediaCollection('featured_image');
        }

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }
}

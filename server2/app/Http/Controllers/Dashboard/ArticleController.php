<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Http\Requests\Settings\SeoAutomationSettingsRequest;
use App\Models\Article;
use App\Models\Service;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Article::class);

        $settings = Settings::firstOrCreate(['id' => 1]);
        $tab = request()->input('tab', 'articles');

        return view('dashboard.articles.index', compact('settings', 'tab'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Article::class);

        $services = Service::query()->orderBy('id')->get();

        return view('dashboard.articles.create', compact('services'));
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

        $article->syncMetaToLinkedService();

        return redirect()->back()->with(['status' => __('ui.created_successfully')]);
    }

    /**
     * Edit article form.
     */
    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        $image = $article->getImageUrl('lg');
        $services = Service::query()->orderBy('id')->get();

        return view('dashboard.articles.edit', compact('article', 'image', 'services'));
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

        $article->fresh()->syncMetaToLinkedService();

        return redirect()->back()->with(['status' => __('ui.updated_successfully')]);
    }

    public function update_seo_automation(SeoAutomationSettingsRequest $request)
    {
        Gate::authorize('viewAny', Article::class);
        Gate::authorize('manage settings');

        $settings = Settings::firstOrCreate(['id' => 1]);

        $settings->update([
            'ai_blog_automation_enabled' => $request->boolean('ai_blog_automation_enabled'),
            'ai_blog_daily_limit' => $request->integer('ai_blog_daily_limit'),
            'ai_blog_monthly_limit' => $request->integer('ai_blog_monthly_limit'),
            'ai_blog_prompt' => $request->input('ai_blog_prompt'),
        ]);

        $settings->updateCache();

        return redirect()
            ->route('dashboard.articles.index', ['tab' => 'seo-automation'])
            ->with(['status' => __('ui.updated_successfully')]);
    }
}

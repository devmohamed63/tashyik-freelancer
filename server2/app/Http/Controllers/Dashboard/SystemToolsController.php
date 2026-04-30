<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;

class SystemToolsController extends Controller
{
    public function index()
    {
        Gate::authorize('manage settings');

        $sitemapExists = file_exists(public_path('sitemaps/index.xml'));
        $sitemapLastGenerated = $sitemapExists
            ? filemtime(public_path('sitemaps/index.xml'))
            : null;

        return view('dashboard.system-tools.index', compact('sitemapExists', 'sitemapLastGenerated'));
    }

    public function generateSitemap()
    {
        Gate::authorize('manage settings');

        Artisan::call('app:generate-sitemaps');

        return redirect()
            ->route('dashboard.system-tools')
            ->with(['status' => __('ui.sitemap_generated_successfully')]);
    }

    public function clearCache()
    {
        Gate::authorize('manage settings');

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return redirect()
            ->route('dashboard.system-tools')
            ->with(['status' => __('ui.cache_cleared_successfully')]);
    }
}

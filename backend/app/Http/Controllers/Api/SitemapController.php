<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (!in_array($request->sitemap, [
            'index.xml',
            'categories.xml',
            'services.xml',
            'articles.xml',
        ])) {
            return abort(404);
        }

        $file = public_path("sitemaps/$request->sitemap");

        return file_exists($file)
            ? response()->file($file)
            : abort(404);
    }
}

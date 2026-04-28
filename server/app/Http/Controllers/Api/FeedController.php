<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    //comment
    public function __invoke(Request $request)
    {
        $allowed = array_map(fn($locale) => "$locale.rss", config('app.available_locales'));

        if (!in_array($request->file, $allowed)) return abort(404);

        $file = public_path("feed/$request->file");

        return file_exists($file)
            ? response()->file($file)
            : abort(404);
    }
}

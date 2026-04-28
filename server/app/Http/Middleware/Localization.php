<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale');

        if (session()->has('locale')) {
            $locale = session()->get('locale');
        }

        if ($request->acceptsJson() && $request->hasHeader('X-App-Language')) {
            $locale = $request->header('X-App-Language') ?? config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}

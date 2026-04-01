<?php

use App\Http\Middleware\EnsureAppIsUpdated;
use App\Http\Middleware\Localization;
use App\Utils\Http\Middleware\TrackApiResponses;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        apiPrefix: '',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'ensure-app-is-updated' => EnsureAppIsUpdated::class,
            'track-api-response' => TrackApiResponses::class,
        ]);

        $middleware->web(append: [
            Localization::class,
        ]);

        $middleware->api(prepend: [
            Localization::class,
            TrackApiResponses::class,
            EnsureAppIsUpdated::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('app:generate-sitemap')->daily();
        $schedule->command('app:generate-product-feed')->daily();
        $schedule->command('app:reset-firestore-analytics')->dailyAt('00:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

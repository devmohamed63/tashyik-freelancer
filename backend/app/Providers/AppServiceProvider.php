<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\User;
use App\Models\Review;
use App\Models\Order;
use App\Observers\ServiceObserver;
use App\Observers\ServiceProviderObserver;
use App\Observers\ServiceProviderOrderObserver;
use App\Observers\ServiceProviderReviewObserver;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(ServiceProviderObserver::class);
        Service::observe(ServiceObserver::class);
        Review::observe(ServiceProviderReviewObserver::class);
        Order::observe(ServiceProviderOrderObserver::class);

        Mail::alwaysFrom(
            (string) config('mail.from.address'),
            (string) config('mail.from.name'),
        );

        // ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
        //     return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        // });
    }
}

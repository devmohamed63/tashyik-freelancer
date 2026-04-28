<?php

namespace App\Providers;

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
        Mail::alwaysFrom(
            (string) config('mail.from.address'),
            (string) config('mail.from.name'),
        );

        // ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
        //     return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        // });
    }
}

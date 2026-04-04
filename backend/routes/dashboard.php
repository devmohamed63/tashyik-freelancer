<?php

use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\ContactController;
use App\Http\Controllers\Dashboard\CouponController;
use App\Http\Controllers\Dashboard\FinancialReportsController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\PromotionController;
use App\Http\Controllers\Dashboard\ReviewsController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\ArticleController;
use App\Http\Controllers\Dashboard\PlanController;
use Illuminate\Support\Facades\Route;

Route::domain(env('DASHBOARD_SUBDOMAIN') . '.' . env('BASE_DOMAIN'))->group(function () {
    Route::middleware(['auth'])->group(function () {

        Route::name('dashboard.')->group(function () {

            // Overview route
            Route::get('/', OverviewController::class)
                ->name('overview');

            // Analytics route
            Route::get('/analytics', AnalyticsController::class)
                ->name('analytics');

            // Financial Reports route
            Route::get('/financial-reports', [FinancialReportsController::class, 'index'])
                ->name('financial-reports');

            // Reviews route
            Route::get('/reviews', [ReviewsController::class, 'index'])
                ->name('reviews');

            // Settings routes start
            Route::prefix('settings')->group(function () {
                Route::name('settings.')->group(function () {

                    Route::get('/', [SettingsController::class, 'index'])
                        ->name('index');

                    Route::put('/basic-information', [SettingsController::class, 'update_basic_information'])
                        ->name('update_basic_information');

                    Route::put('/social-links', [SettingsController::class, 'update_social_links'])
                        ->name('update_social_links');

                    Route::get('/{page}', [SettingsController::class, 'edit_default_page'])
                        ->name('edit_default_page');

                    Route::put('/{page}', [SettingsController::class, 'update_default_page'])
                        ->name('update_default_page');
                });
            });
            // Settings routes end

            // Page routes
            Route::resource('/pages', PageController::class)
                ->except(['show', 'destroy']);

            // Article routes
            Route::resource('/articles', ArticleController::class)
                ->except(['show', 'destroy']);

            // Banner routes
            Route::resource('/banners', BannerController::class)
                ->except(['destroy']);

            // Cities routes
            Route::get('/cities', [CityController::class, 'index'])
                ->name('cities.index');

            // User routes
            Route::resource('/users', UserController::class)
                ->except(['show', 'destroy']);

            // Service providers route
            Route::get('/service-providers', [UserController::class, 'service_providers'])
                ->name('users.service_providers');

            // Payout requests route
            Route::get('/users/payout-requests', [UserController::class, 'payout_requests'])
                ->name('users.payout_requests');

            // Plans route
            Route::get('/plans', PlanController::class)
                ->name('plans.index');

            // Subscriptions route
            Route::get('/subscriptions', SubscriptionController::class)
                ->name('subscriptions.index');

            // Category routes
            Route::get('/categories/children', [CategoryController::class, 'children'])
                ->name('categories.children');

            Route::resource('/categories', CategoryController::class)
                ->except(['destroy']);

            // Services routes
            Route::resource('/services', ServiceController::class)
                ->except(['destroy']);

            // Orders route
            Route::get('/orders', OrderController::class)
                ->name('orders.index');

            // Coupons routes
            Route::get('/coupons', CouponController::class)
                ->name('coupons.index');

            // Promotions routes
            Route::get('/promotions', PromotionController::class)
                ->name('promotions.index');

            // Role routes
            Route::get('/roles', [RoleController::class, 'index'])
                ->name('roles.index');

            // Contact requests route
            Route::get('/contact-requests', [ContactController::class, 'index'])
                ->name('contacts.index');

            // Notification routes
            Route::get('/notifications', NotificationController::class)
                ->name('notifications.index');
        });
    });
});

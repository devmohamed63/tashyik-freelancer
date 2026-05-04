<?php

use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\ArticleController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\PushAdsController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\ChangeEmailController;
use App\Http\Controllers\Dashboard\ChangePasswordController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\ContactController;
use App\Http\Controllers\Dashboard\CouponController;
use App\Http\Controllers\Dashboard\FinancialReportsController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\PlanController;
use App\Http\Controllers\Dashboard\PromotionController;
use App\Http\Controllers\Dashboard\ReviewsController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\TechnicianMapController;
use App\Http\Controllers\Dashboard\SystemToolsController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\PublicInvoiceByTokenController;
use App\Http\Controllers\PublicInvoiceController;
use Illuminate\Support\Facades\Route;

Route::domain(env('DASHBOARD_SUBDOMAIN').'.'.env('BASE_DOMAIN'))->group(function () {
    /** Short public link for emails (secret token, no query string). */
    Route::get('public/i/{view_token}', PublicInvoiceByTokenController::class)
        ->name('public.invoices.token');

    /** Legacy signed URL (still valid for old links). */
    Route::get('public/invoices/{invoice}', PublicInvoiceController::class)
        ->middleware('signed')
        ->name('public.invoices.show');

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

            // Technician Map routes
            Route::get('/technician-map', [TechnicianMapController::class, 'index'])
                ->name('technician-map');

            Route::get('/technician-map/api', [TechnicianMapController::class, 'api'])
                ->name('technician-map.api');

            Route::get('/technician-map/city-insights', [TechnicianMapController::class, 'cityInsights'])
                ->name('technician-map.city-insights');

            Route::get('/technician-map/heatmap-data', [TechnicianMapController::class, 'heatmapData'])
                ->name('technician-map.heatmap-data');

            Route::get('/technician-map/export-excel', [TechnicianMapController::class, 'exportExcel'])
                ->name('technician-map.export-excel');

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

            Route::get('/articles/seo-automation', function () {
                return redirect()->route('dashboard.articles.index', ['tab' => 'seo-automation']);
            })->name('articles.seo_automation');
            Route::match(['post', 'put'], '/articles/seo-automation', [ArticleController::class, 'update_seo_automation'])
                ->name('articles.update_seo_automation');

            // Push ads (FCM): list + compose (mirrors banners index/create)
            Route::get('/push-ads', [PushAdsController::class, 'index'])
                ->name('push-ads.index');

            Route::get('/push-ads/create', [PushAdsController::class, 'create'])
                ->name('push-ads.create');

            Route::resource('/banners', BannerController::class)
                ->except(['destroy']);

            // Cities routes
            Route::get('/cities', [CityController::class, 'index'])
                ->name('cities.index');

            Route::get('/cities/{city}', [CityController::class, 'show'])
                ->name('cities.show');

            // Customers bulk-import: must be declared BEFORE the users resource route
            // so it doesn't collide with /users/{user}/edit.
            Route::get('/users/import/template', [UserController::class, 'import_template'])
                ->name('users.import_template');

            Route::get('/users/import/sample', [UserController::class, 'import_sample_template'])
                ->name('users.import_sample_template');

            // User routes
            Route::resource('/users', UserController::class)
                ->except(['show', 'destroy']);

            // Service providers route
            Route::get('/service-providers', [UserController::class, 'service_providers'])
                ->name('users.service_providers');

            // Add service provider (full page)
            Route::get('/service-providers/create', [UserController::class, 'create_service_provider'])
                ->name('users.create_service_provider');

            // Payout requests route
            Route::get('/users/payout-requests', [UserController::class, 'payout_requests'])
                ->name('users.payout_requests');

            // Institution routes
            Route::get('/institution/{user}', [UserController::class, 'show_institution'])
                ->name('institution.show');

            Route::get('/institution/{user}/export-members', [UserController::class, 'export_members'])
                ->name('institution.export_members');

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

            // Change password routes
            Route::get('/change-password', [ChangePasswordController::class, 'index'])
                ->name('change_password.index');

            Route::put('/change-password', [ChangePasswordController::class, 'update'])
                ->name('change_password.update');

            // Change email routes
            Route::get('/change-email', [ChangeEmailController::class, 'index'])
                ->name('change_email.index');

            Route::put('/change-email', [ChangeEmailController::class, 'update'])
                ->name('change_email.update');

            // System tools routes
            Route::get('/system-tools', [SystemToolsController::class, 'index'])
                ->name('system-tools');

            Route::post('/system-tools/generate-sitemap', [SystemToolsController::class, 'generateSitemap'])
                ->name('system-tools.generate-sitemap');

            Route::post('/system-tools/clear-cache', [SystemToolsController::class, 'clearCache'])
                ->name('system-tools.clear-cache');
        });
    });
});

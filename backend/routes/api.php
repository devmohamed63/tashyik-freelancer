<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Auth\NewPasswordController;
use App\Http\Controllers\Api\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\Auth\RegisteredUserController;
use \App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\RequestPayout;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SitemapController;

use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;

// Service provider
use App\Http\Controllers\Api\ServiceProvider\OrderExtraController as ServiceProviderOrderExtraController;
use App\Http\Controllers\Api\ServiceProvider\OrderController as ServiceProviderOrderController;

// User
use App\Http\Controllers\Api\User\OrderExtraController as UserOrderExtraController;
use App\Http\Controllers\Api\User\OrderController as UserOrderController;

Route::domain(env('API_SUBDOMAIN') . '.' . env('BASE_DOMAIN'))->group(function () {
    Route::name('api.')->group(function () {

        // Auth routes start
        Route::middleware('guest')->group(function () {
            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->name('register');

            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->name('login');

            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

            Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');

            // OTP-based password reset
            Route::post('/password/send-otp', [\App\Http\Controllers\Api\Auth\PasswordResetOtpController::class, 'sendOtp'])
                ->name('password.otp.send');

            Route::post('/password/verify-otp', [\App\Http\Controllers\Api\Auth\PasswordResetOtpController::class, 'verifyOtp'])
                ->name('password.otp.verify');

            Route::post('/password/reset', [\App\Http\Controllers\Api\Auth\PasswordResetOtpController::class, 'resetPassword'])
                ->name('password.otp.reset');
        });
        // Auth routes end

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');

            // Profile routes start
            Route::prefix('profile')->group(function () {
                Route::name('profile.')->group(function () {

                    Route::post('/', [ProfileController::class, 'update'])
                        ->name('update');

                    Route::put('/password', [ProfileController::class, 'update_password'])
                        ->name('update_password');

                    Route::delete('/', [ProfileController::class, 'delete'])
                        ->name('delete');
                });
            });
            // Profile routes start

            // Service routes start
            Route::apiResource('services', ServiceController::class)
                ->only(['show'])
                ->withoutMiddleware('auth:sanctum');

            Route::post('services', [ServiceController::class, 'index'])
                ->name('services.index')
                ->withoutMiddleware('auth:sanctum');

            Route::get('services', [ServiceController::class, 'get_services_for_order_extra'])
                ->name('services.get_services_for_order_extra');
            // Service routes end

            // User routes
            Route::prefix('user')->group(function () {
                Route::name('user.')->group(function () {
                    // Fetch user
                    Route::get('/', function () {
                        return new UserResource(Auth::user());
                    })->name('fetch_user');

                    // Order routes
                    Route::apiResource('orders', UserOrderController::class)
                        ->except(['update']);

                    // Order extra routes
                    Route::apiResource('order-extra', UserOrderExtraController::class)
                        ->only(['index', 'show']);
                });
            });

            // Service provider routes
            Route::prefix('service_provider')->group(function () {
                Route::name('service_provider.')->group(function () {
                    // Order routes
                    Route::apiResource('orders', ServiceProviderOrderController::class)
                        ->except(['store']);

                    // Order extra routes
                    Route::apiResource('order-extra', ServiceProviderOrderExtraController::class)
                        ->only(['index', 'store']);
                });
            });

            // Wallet routes
            Route::get('wallet/balance', [WalletController::class, 'balance'])
                ->name('wallet.balance');

            // Notification routes
            Route::resource('notifications', NotificationController::class)
                ->only(['index', 'show']);

            // Invoices
            Route::get('invoices', [InvoiceController::class, 'index'])
                ->name('invoices.index');

            // Subscription routes start
            Route::prefix('subscription')->group(function () {
                Route::name('subscription.')->group(function () {
                    Route::get('/', function() { return response()->json([]); })
                        ->name('index');
                });
            });
            // Subscription routes end

            // Plan routes
            Route::apiResource('plans', PlanController::class)
                ->only(['index', 'show']);

            // Payout request route
            Route::get('request-payout', RequestPayout::class)
                ->name('request_payout');

            // Address routes
            Route::apiResource('addresses', AddressController::class);

            // Review routes
            Route::apiResource('reviews', ReviewController::class)
                ->only(['store']);
        });

        // General routes start
        Route::prefix('general')->group(function () {
            Route::name('general.')->group(function () {
                Route::get('/default-pages/{page}', [GeneralController::class, 'show_default_page'])
                    ->name('show_default_page');

                Route::get('/layout', [GeneralController::class, 'layout'])
                    ->name('layout');

                Route::middleware('auth:sanctum')->group(function () {

                    // Update user location
                    Route::put('/update-user-location', [GeneralController::class, 'update_user_location'])
                        ->name('update_user_location');

                    // Update user FCM token
                    Route::put('/update-fcm-token', [GeneralController::class, 'update_fcm_token'])
                        ->name('update_fcm_token');

                    // Welcome coupon
                    Route::get('/welcome-coupon', [GeneralController::class, 'get_welcome_coupon'])
                        ->name('get_welcome_coupon');
                });

                // Service collections
                Route::get('/service-collections', [GeneralController::class, 'service_collections'])
                    ->name('service_collections');

                // Questions
                Route::get('/questions', [GeneralController::class, 'questions'])
                    ->name('questions');

                // App mode
                Route::get('/app-mode', [GeneralController::class, 'get_app_mode'])
                    ->name('get_app_mode');
            });
        });
        // General routes end

        // City requests
        Route::apiResource('/cities', CityController::class)
            ->only('index');

        // Category routes
        Route::apiResource('categories', CategoryController::class)
            ->only(['index', 'show']);

        // Banner routes
        Route::apiResource('/banners', BannerController::class)
            ->only('index');

        // Contact requests
        Route::apiResource('/contact-requests', ContactController::class)
            ->only('store');

        // Page routes
        Route::apiResource('/pages', PageController::class)
            ->only(['index', 'show']);

        // Article routes
        Route::get('/articles', [\App\Http\Controllers\Api\ArticleController::class, 'index'])
            ->name('articles.index');
        Route::get('/articles/{article:slug}', [\App\Http\Controllers\Api\ArticleController::class, 'show'])
            ->name('articles.show');

        Route::withoutMiddleware('ensure-app-is-updated')->group(function () {

            // Webhook routes
            Route::post('/webhook/paymob', [WebhookController::class, 'paymob'])
                ->name('webhook.paymob');

            Route::withoutMiddleware('track-api-response')->group(function () {
                // Sitemap routes
                Route::get('/sitemaps/{sitemap}', SitemapController::class)
                    ->name('sitemaps.show');

                // RSS Feed
                Route::get('/feed/{file}', FeedController::class)
                    ->name('feed.show');
            });
        });
    });
});

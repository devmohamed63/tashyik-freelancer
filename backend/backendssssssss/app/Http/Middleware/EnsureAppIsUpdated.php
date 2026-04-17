<?php

namespace App\Http\Middleware;

use App\Utils\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class EnsureAppIsUpdated
{
    use ApiResponse;

    const CUSTOMER_APP_TYPE = 'customer';

    const SERVICE_PROVIDER_APP_TYPE = 'service_provider';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) return $next($request);

        $currentVersion = null;
        $updateRequired = null;

        $requestAppType = $request->header('X-App-Type') ?? $request->header('app_type');
        $requestAppVersion = $request->header('X-App-Version');

        if ($requestAppType == self::CUSTOMER_APP_TYPE) {
            $currentVersion = env('CUSTOMER_APP_VERSION');
            $updateRequired = env('CUSTOMER_APP_UPDATE_REQUIRED');
        } elseif ($requestAppType == self::SERVICE_PROVIDER_APP_TYPE) {
            $currentVersion = env('SERVICE_PROVIDER_APP_VERSION');
            $updateRequired = env('SERVICE_PROVIDER_APP_UPDATE_REQUIRED');
        }

        if ($updateRequired && $requestAppVersion < $currentVersion) {
            $message = [
                'ar' => 'يجب تحديث التطبيق',
                'en' => 'App update required',
                'hi' => 'एप को अपडेट करना आवश्यक है',
                'bn' => 'অ্যাপ আপডেট প্রয়োজন',
                'ur' => 'ایپ کو اپ ڈیٹ کرنا ضروری ہے',
                'tl' => 'Kinakailangan ang pag-update ng app',
                'id' => 'Pembaruan aplikasi diperlukan',
                'fr' => 'Mise à jour de l’application requise',
            ];

            $data = [
                'app_links' => [
                    'user' => [
                        'google_play' => env('CUSTOMER_APP_URL_GOOGLE_PLAY'),
                        'app_store' => env('CUSTOMER_APP_URL_APP_STORE'),
                    ],
                    'service_provider' => [
                        'google_play' => env('SERVICE_PROVIDER_APP_URL_GOOGLE_PLAY'),
                        'app_store' => env('SERVICE_PROVIDER_APP_URL_APP_STORE'),
                    ]
                ]
            ];

            return $this->apiResponse($data, $message, false, 426);
        }

        return $next($request);
    }
}

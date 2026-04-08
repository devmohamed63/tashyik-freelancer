<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Page;
use App\Models\Coupon;
use App\Models\Question;
use App\Models\ServiceCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\ServiceCollectionResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function show_default_page($pageName)
    {
        $page = Page::getDefaultPage($pageName);

        return new PageResource($page);
    }

    public function update_user_location(Request $request)
    {
        $request->validate([
            'city' => ['nullable', 'integer', 'exists:cities,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        /**
         * @var User
         */
        $user = Auth::user();
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->city_id = $request->city;
        $user->save();

        return response(null);
    }

    public function update_fcm_token(Request $request)
    {
        $request->validate([
            'token' => ['nullable', 'string', 'max:255'],
            'ui_locale' => ['nullable', 'string', 'max:255'],
        ]);

        /**
         * @var User
         */
        $user = Auth::user();

        $user->update([
            'ui_locale' => $request->ui_locale,
            'fcm_token' => $request->token,
        ]);

        return response('');
    }

    public function layout()
    {
        $settings = Cache::get('settings');

        $description = $settings?->description;
        $logoLightMode = Cache::get('light_mode_logo');
        $logoDarkMode = Cache::get('dark_mode_logo');

        $user = Auth::guard('sanctum')?->user();

        return [
            'logo' => [
                'light_mode' => $logoLightMode,
                'dark_mode' => $logoDarkMode,
            ],
            'description' => $description,
            'social_links' => [
                'facebook' => $settings?->facebook_url,
                'twitter' => $settings?->twitter_url,
                'instagram' => $settings?->instagram_url,
                'snapchat' => $settings?->snapchat_url,
                'tiktok' => $settings?->tiktok_url,
            ],
            'mobile_app_links' => [
                'google_play' => env('CUSTOMER_APP_URL_GOOGLE_PLAY'),
                'app_store' => env('CUSTOMER_APP_URL_APP_STORE'),
            ],
            'user' => $user ? new UserResource($user) : null,
            'contact_info' => [
                'phone_number' => $settings?->phone_number,
                'whatsapp_link' => $settings?->whatsapp_link,
                'email' => $settings?->email,
            ]
        ];
    }

    /**
     * Get list of the featured services for home page
     */
    public function service_collections()
    {
        $collections = ServiceCollection::with([
            'services:id,promotion_id,name,slug,price',
            'services.promotion',
            'services.media',
        ])
            ->orderBy('item_order')
            ->get([
                'id',
                'title',
            ]);

        return ServiceCollectionResource::collection($collections);
    }

    /**
     * Get list of the question and answers
     */
    public function questions()
    {
        $questions = Question::orderBy('item_order')
            ->get([
                'id',
                'title',
                'answer',
            ]);

        return QuestionResource::collection($questions);
    }

    /**
     * Get the welcome coupon for new customers
     */
    public function get_welcome_coupon()
    {
        $user = Auth::guard('sanctum')?->user();

        // Check if user has used the welcome coupon
        if (!$user->used_welcome_coupon) {
            $coupon = Coupon::where('welcome', true)->first();

            $data = [
                'code' => $coupon?->code,
                'value' => $coupon?->getValue(),
            ];
        }

        return response()->json([
            'coupon' => isset($coupon) ? $data : null,
        ]);
    }

    public function get_app_mode()
    {
        return response()->json([
            'mode' => app()->environment(),
        ]);
    }
}

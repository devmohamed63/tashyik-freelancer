<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Settings::create([
            'name' => [
                'ar' => 'تشييك',
                'en' => 'Tashyik',
            ],
            'description' => 'تشييك هي منصة متخصصة في تقديم خدمات الصيانة المنزلية والإنشائية في جميع مدن المملكة العربية السعودية، حيث نوفر لك أفضل الفنيين المتخصصين في مجالات النجارة، السباكة، الكهرباء، وأعمال الصيانة الأخرى.',
            'phone_number' => '+966 53 044 6151',
            'whatsapp_link' => 'http://wa.me/+966530446151',
            'email' => 'contact@tashyik.com',
            'facebook_url' => fake()->url(),
            'twitter_url' => fake()->url(),
            'instagram_url' => fake()->url(),
            'snapchat_url' => fake()->url(),
            'tiktok_url' => fake()->url(),
        ]);

        Cache::forget('settings');

        Cache::rememberForever('settings', fn() => Settings::first());

        Cache::forever('light_mode_logo', asset('images/logo/light_mode_logo.png'));
        Cache::forever('dark_mode_logo', asset('images/logo/dark_mode_logo.png'));
        Cache::forever('icon', asset('images/icons/default.png'));
    }
}

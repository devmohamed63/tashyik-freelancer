<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Events\NewUser;
use App\Models\Category;
use App\Models\City;
use App\Models\Page;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->call([
            BannerSeeder::class,
            CitySeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            AddressSeeder::class,
            ServiceSeeder::class,
            OrderSeeder::class,
            OrderExtraSeeder::class,
            InvoiceSeeder::class,
            NotificationSeeder::class,
            CouponSeeder::class,
            PromotionSeeder::class,
            ServiceCollectionSeeder::class,
            HighlightSeeder::class,
            ReviewSeeder::class,
            QuestionSeeder::class,
            ContactSeeder::class,
        ]);
    }
}

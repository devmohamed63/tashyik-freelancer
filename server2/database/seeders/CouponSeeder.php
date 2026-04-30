<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            "عرض اليوم",
            "خصم نهاية الاسبوع",
            "صفقة اليوم",
            "عرض نهاية الأسبوع",
            "عرض الجمعة البيضاء",
            "عرض خاص",
            "عرض محدود",
            "خصم الشتاء",
            "خصم الصيف",
            "عرض الموسم",
            "العرض الكبير",
            "خصم الموسم",
        ];

        foreach ($coupons as $coupon) {
            $coupon = Coupon::factory()->create([
                'name' => $coupon,
            ]);

            $coupon->services()->attach(Service::pluck('id')->toArray());
            $coupon->categories()->attach(Category::pluck('id')->toArray());
        }

        // Create welcome coupon
        $coupon = Coupon::factory()->create([
            'name' => 'خصم ترحيبي',
            'welcome' => true,
        ]);

        $coupon->services()->attach(Service::pluck('id')->toArray());
        $coupon->categories()->attach(Category::pluck('id')->toArray());
    }
}

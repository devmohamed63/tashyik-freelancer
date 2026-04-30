<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locale = config('app.fallback_locale');

        $cityNames = [
            "الرياض",
            "جدة",
            "مكة المكرمة",
            "المدينة المنورة",
            "الدمام",
            "الهفوف",
            "الطائف",
            "القصيم (بريدة)",
            "تبوك",
            "الخبر",
            "أبها",
            "خميس مشيط",
            "جازان",
            "حائل",
            "نجران",
            "ينبع",
            "الجبيل",
        ];

        foreach ($cityNames as $name) {
            City::factory()->create([
                'name' => [
                    $locale => $name
                ]
            ]);
        }
    }
}

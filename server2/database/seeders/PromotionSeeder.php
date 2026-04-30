<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = Promotion::factory(5)->create();

        foreach ($promotions as $promotion) {
            Service::inRandomOrder()->limit(5)->update([
                'promotion_id' => $promotion->id,
            ]);
        }
    }
}

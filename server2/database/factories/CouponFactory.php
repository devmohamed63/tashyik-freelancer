<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => Str::random(8),
            'target' => fake()->randomElement(Coupon::AVAILABLE_TARGET_TYPES),
            'type' => fake()->randomElement(Coupon::AVAILABLE_TYPES),
            'value' => rand(10, 150),
            'usage_times' => rand(0, 100),
            'welcome' => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\OrderExtra;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderExtra>
 */
class OrderExtraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::inRandomOrder()->first('id')?->id,
            'status' => fake()->randomElement(OrderExtra::AVAILABLE_STATUS_TYPES),
            'quantity' => fake()->numberBetween(1, 3),
            'price' => fake()->numberBetween(50, 200),
            'tax_rate' => fake()->numberBetween(5, 20),
            'tax' => fake()->numberBetween(10, 50),
            'wallet_balance' => fake()->numberBetween(0, 200),
            'materials' => rand(20, 700),
            'total' => fake()->numberBetween(100, 300),
        ];
    }
}

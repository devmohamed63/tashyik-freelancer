<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
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
            'target_group' => fake()->randomElement(User::AVAILABLE_ENTITY_TYPES),
            'price' => rand(100, 500),
            'duration_in_days' => rand(30, 90),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(asText: true),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'address' => fake()->address(),
            'landmark' => fake()->words(asText: true),
            'building_number' => fake()->numberBetween(100, 300),
            'floor_number' => fake()->numberBetween(1, 6),
            'apartment_number' => fake()->numberBetween(1, 12),
            'is_default' => fake()->boolean(),
        ];
    }
}

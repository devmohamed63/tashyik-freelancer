<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(),
            'name' => fake()->name(),
            'phone' => '05' . fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'message' => fake()->paragraph(),
        ];
    }
}

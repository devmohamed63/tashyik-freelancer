<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                'ar' => fake()->words(3, true),
                'en' => fake('en_US')->words(3, true),
            ],
            'body' => [
                'ar' => fake()->words(8, true),
                'en' => fake('en_US')->words(8, true),
            ],
            'status' => Page::ACTIVE_STATUS
        ];
    }
}

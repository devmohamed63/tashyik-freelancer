<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ratings = range(10, 100, 10);

        $generalReviews = [
            "تجربة ممتازة بشكل عام، جودة كويسة، والتعامل كان احترافي. أنصح بالتجربة.",
            "تجربة إيجابية، الجودة جيدة، والتعامل منظم وواضح.",
            "مستوى الخدمة مرضي، والتنفيذ مطابق للتوقعات.",
            "تعامل محترم وجودة مناسبة، تجربة تستحق التكرار.",
            "انطباع عام إيجابي، والتجربة كانت سلسة."
        ];

        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'body' => fake()->randomElement($generalReviews),
            'rating' => $ratings[array_rand($ratings)],
        ];
    }
}

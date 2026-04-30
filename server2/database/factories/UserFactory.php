<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'balance' => rand(0, 2000),
            'city_id' => City::query()->inRandomOrder()->first('id')?->id,
            'bank_name' => fake()->words(asText: true),
            'entity_type' => fake()->randomElement(User::AVAILABLE_ENTITY_TYPES),
            'residence_name' => fake()->name(),
            'residence_number' => mt_rand(),
            'iban' => fake()->iban(),
            'status' => fake()->randomElement(User::AVAILABLE_STATUS_TYPES),
            'commercial_registration_number' => mt_rand(),
            'tax_registration_number' => mt_rand(),
        ];
    }
}

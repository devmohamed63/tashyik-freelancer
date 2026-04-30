<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceProviders = User::notUser()->get(['id']);

        foreach ($serviceProviders as $serviceProvider) {
            $reviews = Review::factory(rand(0, 3))->make()->toArray();

            /**
             * @var User $serviceProvider
             */
            $serviceProvider->reviews()->createMany($reviews);
        }
    }
}

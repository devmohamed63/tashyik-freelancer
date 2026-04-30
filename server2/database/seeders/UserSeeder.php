<?php

namespace Database\Seeders;

use App\Events\NewUser;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Demo user
        User::factory()->create([
            'id' => 2001,
            'name' => 'كريم أيمن',
            'phone' => '0000000000',
            'password' => Hash::make('12345678'),
            'status' => User::ACTIVE_STATUS,
        ]);

        // Demo service provider
        User::factory()->create([
            'id' => 2002,
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'name' => 'عاطف أمين',
            'phone' => '1111111111',
            'password' => Hash::make('12345678'),
            'latitude' => null,
            'longitude' => null,
            'status' => User::ACTIVE_STATUS,
        ]);

        // Random users
        User::factory(10)->create();

        // Random service providers
        User::factory(10)->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
        ]);

        $serviceProviders = User::isServiceProvider()->get([
            'id', # Get ID to link with categories
            'type' # Get type new user notification
        ]);

        $categoryIds = Category::query()->isParent()->pluck('id')->toArray();

        foreach ($serviceProviders as $serviceProvider) {
            $serviceProvider->categories()->attach($categoryIds);

            NewUser::dispatch($serviceProvider);
        }

        $institutionIds = User::isServiceProvider()->isInstitution()->pluck('id')->toArray();

        // Create members for the institutions
        foreach ($institutionIds as $id) {
            User::factory(4)->create([
                'institution_id' => $id,
                'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
                'entity_type' => User::INDIVIDUAL_ENTITY_TYPE,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCollection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCollections = ServiceCollection::factory(3)->create();

        foreach ($serviceCollections as $collection) {
            $serviceIds = Service::query()
                ->inRandomOrder()
                ->limit(6)
                ->pluck('id')
                ->toArray();

            $collection->services()->attach($serviceIds);
        }
    }
}

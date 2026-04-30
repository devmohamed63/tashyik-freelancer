<?php

namespace Database\Seeders;

use App\Models\Highlight;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HighlightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceIds = Service::pluck('id')->toArray();

        foreach ($serviceIds as $id) {
            Highlight::factory(rand(3, 6))->create([
                'service_id' => $id,
            ]);
        }
    }
}

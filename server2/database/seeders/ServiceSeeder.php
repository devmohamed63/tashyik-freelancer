<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::isChild()->get(['id']);

        foreach ($categoryIds as $categoryId) {
            Service::factory(rand(4, 8))->create([
                'category_id' => $categoryId
            ]);
        }
    }
}

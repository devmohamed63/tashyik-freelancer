<?php

// Seed Firebase Firestore Analytics with random data.

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Utils\Services\Firebase\Firestore;
use App\Models\Category;
use App\Models\City;

$firestore = new Firestore();

// 1. Get Categories IDs
$categoryIds = Category::isParent()->pluck('id')->toArray();

// 2. Get City IDs 
$cityIds = City::pluck('id')->toArray();

// Prepare increment fields array
// Structure: [ ['path/doc_id', 'field_name', increment_value] ]
$increments = [];

// Seed categories
foreach ($categoryIds as $id) {
    if (rand(0, 1) === 1) continue; // 50% chance to skip
    $increments[] = [
        'category_analytics/' . $id,
        'count',
        rand(15, 120)
    ];
}

// Seed cities
foreach ($cityIds as $id) {
    if (rand(0, 1) === 1) continue;
    $increments[] = [
        'city_analytics/' . $id,
        'count',
        rand(5, 50)
    ];
}

if (!empty($increments)) {
    echo "Seeding Firestore with random counts...\n";
    $firestore->incrementFields($increments);
    echo "Done! Refresh the dashboard analytics page to see the numbers.\n";
} else {
    echo "Randomizer chose not to seed anything this run. Try again!\n";
}

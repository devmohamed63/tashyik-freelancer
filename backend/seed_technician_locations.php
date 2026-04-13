<?php

/**
 * Seed fake GPS locations for service providers around Saudi cities.
 * 
 * Usage: php seed_technician_locations.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Saudi Arabian cities with their center coordinates
$cities = [
    ['name' => 'الرياض',    'lat' => 24.7136, 'lng' => 46.6753],
    ['name' => 'جدة',       'lat' => 21.5433, 'lng' => 39.1728],
    ['name' => 'الدمام',    'lat' => 26.4207, 'lng' => 50.0888],
    ['name' => 'مكة',       'lat' => 21.4225, 'lng' => 39.8262],
    ['name' => 'المدينة',   'lat' => 24.4686, 'lng' => 39.6142],
    ['name' => 'بريدة',     'lat' => 26.3263, 'lng' => 43.9717],
    ['name' => 'تبوك',      'lat' => 28.3838, 'lng' => 36.5550],
    ['name' => 'خميس مشيط', 'lat' => 18.3066, 'lng' => 42.7350],
    ['name' => 'أبها',      'lat' => 18.2164, 'lng' => 42.5053],
    ['name' => 'حائل',      'lat' => 27.5219, 'lng' => 41.6907],
];

$technicians = User::where('type', User::SERVICE_PROVIDER_ACCOUNT_TYPE)
    ->where('status', User::ACTIVE_STATUS)
    ->get();

if ($technicians->isEmpty()) {
    echo "⚠️  No active service providers found in the database.\n";
    exit(1);
}

echo "🗺️  Seeding locations for {$technicians->count()} technicians...\n\n";

$onlineCount = 0;
$offlineCount = 0;

foreach ($technicians as $index => $tech) {
    // Pick a random Saudi city
    $city = $cities[array_rand($cities)];

    // Generate random offset (within ~15km radius of city center)
    $latOffset = (mt_rand(-150, 150) / 10000);
    $lngOffset = (mt_rand(-150, 150) / 10000);

    $latitude = round($city['lat'] + $latOffset, 6);
    $longitude = round($city['lng'] + $lngOffset, 6);

    // 60% chance of being online (seen within last 5 minutes)
    // 40% chance of being offline (seen 1-24 hours ago)
    $isOnline = mt_rand(1, 100) <= 60;

    if ($isOnline) {
        $lastSeenAt = now()->subMinutes(mt_rand(0, 4)); // 0-4 minutes ago
        $onlineCount++;
    } else {
        $lastSeenAt = now()->subHours(mt_rand(1, 24)); // 1-24 hours ago
        $offlineCount++;
    }

    $tech->update([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'last_seen_at' => $lastSeenAt,
    ]);

    $status = $isOnline ? '🟢' : '⚫';
    echo "  {$status} {$tech->name} → {$city['name']} ({$latitude}, {$longitude})\n";
}

echo "\n✅ Done!\n";
echo "   🟢 Online: {$onlineCount}\n";
echo "   ⚫ Offline: {$offlineCount}\n";
echo "   📊 Total: {$technicians->count()}\n";

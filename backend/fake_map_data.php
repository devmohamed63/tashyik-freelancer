<?php

use App\Models\City;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$riyadh = City::where('name', 'like', '%رياض%')->first();
if (!$riyadh) {
    echo "Creating Riyadh...\n";
    $riyadhId = DB::table('cities')->insertGetId(['name' => json_encode(['ar' => 'الرياض', 'en' => 'Riyadh']), 'item_order' => 1, 'latitude' => 24.7136, 'longitude' => 46.6753, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
} else {
    $riyadh->update(['latitude' => 24.7136, 'longitude' => 46.6753]);
    $riyadhId = $riyadh->id;
}

$jeddah = City::where('name', 'like', '%جدة%')->orWhere('name', 'like', '%جده%')->first();
if (!$jeddah) {
    echo "Creating Jeddah...\n";
    $jeddahId = DB::table('cities')->insertGetId(['name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']), 'item_order' => 2, 'latitude' => 21.5433, 'longitude' => 39.1728, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
} else {
    $jeddah->update(['latitude' => 21.5433, 'longitude' => 39.1728]);
    $jeddahId = $jeddah->id;
}

$categories = Category::isParent()->get();
if ($categories->isEmpty()) {
    $cat1Id = DB::table('categories')->insertGetId(['name' => json_encode(['ar' => 'سباكة']), 'status' => 1]);
    $cat2Id = DB::table('categories')->insertGetId(['name' => json_encode(['ar' => 'كهرباء']), 'status' => 1]);
    $categoryIds = [$cat1Id, $cat2Id];
} else {
    $categoryIds = $categories->pluck('id')->toArray();
}


$customer = User::where('type', User::CUSTOMER_ACCOUNT_TYPE)->first();
if (!$customer) {
    $customerId = DB::table('users')->insertGetId([
        'name' => 'عميل وهمي',
        'phone' => '0599999999',
        'password' => Hash::make('password'),
        'type' => User::CUSTOMER_ACCOUNT_TYPE,
        'status' => 'active',
        'created_at' => now(),
    ]);
} else {
    $customerId = $customer->id;
}

$service = DB::table('services')->first();
$serviceId = $service ? $service->id : null;

if (!$serviceId) {
    if(!empty($categoryIds)) {
        $serviceId = DB::table('services')->insertGetId([
            'category_id' => $categoryIds[0],
            'name' => json_encode(['ar' => 'خدمة وهمية']),
            'status' => 1,
            'price_type' => 'fixed',
            'fixed_price' => 100,
        ]);
    }
}

// Hook categories to cities
foreach ([$riyadhId, $jeddahId] as $cId) {
    foreach ($categoryIds as $catId) {
        DB::table('category_city')->insertOrIgnore(['category_id' => $catId, 'city_id' => $cId]);
    }
}


function getRandomLocation($lat, $lng, $radiusInKm = 40) {
    $r = $radiusInKm / 111.300; 
    $u = mt_rand(0, 100) / 100;
    $v = mt_rand(0, 100) / 100;
    $w = $r * sqrt($u);
    $t = 2 * pi() * $v;
    $x = $w * cos($t);
    $y = $w * sin($t);
    $newLat = $lat + $y;
    $newLng = $lng + $x / cos(deg2rad($lat));
    return [$newLat, $newLng];
}

$cities = [
    ['id' => $riyadhId, 'lat' => 24.7136, 'lng' => 46.6753],
    ['id' => $jeddahId, 'lat' => 21.5433, 'lng' => 39.1728],
];

echo "Generating Technicians...\n";

foreach ($cities as $city) {
    for ($i = 0; $i < 20; $i++) {
        [$lat, $lng] = getRandomLocation($city['lat'], $city['lng']);
        $lastSeen = rand(0, 1) ? now()->subMinutes(rand(1, 10)) : now()->subHours(rand(1, 24));
        
        $techId = DB::table('users')->insertGetId([
            'name' => 'فني ' . rand(100, 999),
            'phone' => '05' . rand(10000000, 99999999),
            'password' => Hash::make('12345678'),
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'entity_type' => 'individual',
            'status' => 'active',
            'city_id' => $city['id'],
            'latitude' => $lat,
            'longitude' => $lng,
            'last_seen_at' => $lastSeen,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // random category
        DB::table('category_user')->insert([
            'category_id' => $categoryIds[array_rand($categoryIds)],
            'user_id' => $techId
        ]);
    }
}

echo "Generating Orders for Heatmap...\n";
foreach ($cities as $city) {
    for ($i = 0; $i < 50; $i++) {
        [$lat, $lng] = getRandomLocation($city['lat'], $city['lng']);
        
        $addressId = DB::table('addresses')->insertGetId([
            'user_id' => $customerId,
            'city_id' => $city['id'],
            'latitude' => $lat,
            'longitude' => $lng,
            'address' => 'Fake Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $status = rand(0, 1) ? 'completed' : 'new';
        $createdAt = ($status == 'new' && rand(0,1)) ? now()->subHours(rand(2, 5)) : now()->subDays(rand(0, 6));

        DB::table('orders')->insert([
            'order_id' => 'ORD-' . rand(1000, 9999) . $i,
            'user_id' => $customerId,
            'service_provider_id' => null,
            'service_id' => $serviceId,
            'address_id' => $addressId,
            'status' => $status,
            'subtotal' => 100,
            'total' => 100,
            'payment_method' => 'cash',
            'latitude' => $lat,
            'longitude' => $lng,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}

echo "Fake Data Generated Successfully!\n";

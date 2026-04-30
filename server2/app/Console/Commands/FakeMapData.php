<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\City;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FakeMapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fake-map-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake map data for technicians and orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Fake Data Generation...');

        $riyadh = City::where('name', 'like', '%رياض%')->first();
        if (!$riyadh) {
            $riyadhId = DB::table('cities')->insertGetId(['name' => json_encode(['ar' => 'الرياض', 'en' => 'Riyadh']), 'item_order' => 1, 'latitude' => 24.7136, 'longitude' => 46.6753]);
        } else {
            $riyadh->update(['latitude' => 24.7136, 'longitude' => 46.6753]);
            $riyadhId = $riyadh->id;
        }

        $jeddah = City::where('name', 'like', '%جدة%')->orWhere('name', 'like', '%جده%')->first();
        if (!$jeddah) {
            $jeddahId = DB::table('cities')->insertGetId(['name' => json_encode(['ar' => 'جدة', 'en' => 'Jeddah']), 'item_order' => 2, 'latitude' => 21.5433, 'longitude' => 39.1728]);
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

        $customer = User::where('type', User::USER_ACCOUNT_TYPE)->first();
        if (!$customer) {
            $customerId = DB::table('users')->insertGetId([
                'name' => 'عميل وهمي',
                'phone' => '05' . rand(10000000, 99999999), // unique phone
                'password' => Hash::make('password'),
                'type' => User::USER_ACCOUNT_TYPE,
                'status' => 'active',
                'created_at' => now(),
            ]);
        } else {
            $customerId = $customer->id;
        }

        $service = DB::table('services')->first();
        $serviceId = $service ? $service->id : null;

        if (!$serviceId && !empty($categoryIds)) {
            $serviceId = DB::table('services')->insertGetId([
                'category_id' => $categoryIds[0],
                'name' => json_encode(['ar' => 'خدمة وهمية']),
                'status' => 1,
                'price_type' => 'fixed',
                'fixed_price' => 100,
            ]);
        }

        foreach ([$riyadhId, $jeddahId] as $cId) {
            foreach ($categoryIds as $catId) {
                DB::table('category_city')->insertOrIgnore(['category_id' => $catId, 'city_id' => $cId]);
            }
        }

        $cities = [
            ['id' => $riyadhId, 'lat' => 24.7136, 'lng' => 46.6753],
            ['id' => $jeddahId, 'lat' => 21.5433, 'lng' => 39.1728],
        ];

        $this->info("Generating Technicians...");

        foreach ($cities as $city) {
            for ($i = 0; $i < 20; $i++) {
                $lat = $city['lat'] + (rand(-300, 300) / 10000); // approx within 30km
                $lng = $city['lng'] + (rand(-300, 300) / 10000);
                $lastSeen = rand(0, 1) ? now()->subMinutes(rand(1, 10)) : now()->subHours(rand(1, 24));
                
                $techId = DB::table('users')->insertGetId([
                    'name' => 'فني ' . rand(100, 999),
                    'phone' => '05' . rand(1000000, 9999999) . rand(0, 9),
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
                
                DB::table('category_user')->insert([
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'user_id' => $techId
                ]);
            }
        }

        $this->info("Generating Orders for Heatmap...");
        foreach ($cities as $city) {
            for ($i = 0; $i < 50; $i++) {
                $lat = $city['lat'] + (rand(-300, 300) / 10000);
                $lng = $city['lng'] + (rand(-300, 300) / 10000);
                
                $addressId = DB::table('addresses')->insertGetId([
                    'user_id' => $customerId,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'name' => 'Home',
                    'is_default' => 0,
                    'address' => 'Fake Address'
                ]);
                
                $status = rand(0, 1) ? 'completed' : 'new';
                $createdAt = ($status == 'new' && rand(0,1)) ? now()->subHours(rand(2, 5)) : now()->subDays(rand(0, 6));

                DB::table('orders')->insert([
                    'customer_id' => $customerId,
                    'category_id' => $categoryIds[0],
                    'service_provider_id' => null,
                    'service_id' => $serviceId,
                    'address_id' => $addressId,
                    'status' => $status,
                    'quantity' => 1,
                    'visit_cost' => 0,
                    'tax_rate' => 0,
                    'tax' => 0,
                    'coupons_total' => 0,
                    'wallet_balance' => 0,
                    'subtotal' => 100,
                    'total' => 100,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->info('Fake Data Generated Successfully!');
    }
}

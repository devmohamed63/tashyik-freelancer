<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerIds = User::isUser()->pluck('id')->toArray();

        foreach ($customerIds as $id) {
            // New orders
            Order::factory(5)->create([
                'customer_id' => $id,
                'service_provider_id' => null,
                'status' => Order::NEW_STATUS,
                'latitude' => mt_rand(293214, 293845) / 10000,
                'longitude' => 30.6742,
            ]);

            $acceptedOrderStatus = [
                Order::SERVICE_PROVIDER_ON_THE_WAY,
                Order::SERVICE_PROVIDER_ARRIVED,
                Order::STARTED_STATUS,
            ];

            foreach ($acceptedOrderStatus as $status) {
                Order::factory(1)->create([
                    'customer_id' => $id,
                    'status' => $status,
                ]);
            }

            // Completed orders
            Order::factory(3)->create([
                'customer_id' => $id,
                'status' => Order::COMPLETED_STATUS,
                'service_provider_id' => 2002,
            ]);
        }
    }
}

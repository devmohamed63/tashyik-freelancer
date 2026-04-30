<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderExtra;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderExtraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderIds = Order::whereNot('status', Order::NEW_STATUS)->pluck('id')->toArray();

        foreach ($orderIds as $id) {
            OrderExtra::factory(5)->create(['order_id' => $id]);
        }
    }
}

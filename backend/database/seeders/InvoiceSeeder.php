<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceProviders = User::notUser()->get(['id']);

        foreach ($serviceProviders as $serviceProvider) {
            $invoices = Invoice::factory(5)->make();

            $serviceProvider->invoices()->saveMany($invoices);
        }
    }
}

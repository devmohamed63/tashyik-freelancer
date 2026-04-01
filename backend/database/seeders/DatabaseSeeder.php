<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            RolesAndPermissionsSeeder::class,
            PageSeeder::class,
            PlanSeeder::class,
        ]);

        // Create admin account
        $admin = User::create([
            'name' => 'Admin Account',
            'phone' => '429399600',
            'password' => Hash::make('jS88#va2C8nnMS'),
        ]);

        $admin->assignRole('Super admin');
    }
}

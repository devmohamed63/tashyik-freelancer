<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissionsArray = [

            'General' => [
                'view dashboard',
                'manage roles and permissions',
                'manage contact requests',
                'manage settings',
                'manage orders',
                'manage subscriptions',
                'manage coupons',
                'manage promotions',
                'manage plans',
            ],

            'Cities' => [
                'view cities',
                'create cities',
                'update cities',
                'delete cities',
            ],

            'Categories' => [
                'view categories',
                'create categories',
                'update categories',
                'delete categories',
            ],

            'Users' => [
                'view users',
                'create users',
                'update users',
                'delete users',
                'restore users',
                'force delete users',
            ],

            'Services' => [
                'view services',
                'create services',
                'update services',
                'delete services',
            ],

            'Banners' => [
                'view banners',
                'create banners',
                'update banners',
                'delete banners'
            ],

            'Pages' => [
                'view pages',
                'create pages',
                'update pages',
                'delete pages',
            ],

        ];

        foreach ($permissionsArray as $tag => $permissions) {
            foreach ($permissions as $permission) {
                Permission::create([
                    'tag_name' => $tag,
                    'name' => $permission
                ]);
            }
        }

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions

        $superAdminRole = Role::create([
            'id' => 1,
            'name' => 'Super admin'
        ]);

        $superAdminRole->givePermissionTo(Permission::all());
    }
}

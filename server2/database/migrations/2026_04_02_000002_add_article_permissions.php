<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view articles' => 'Articles',
            'create articles' => 'Articles',
            'update articles' => 'Articles',
            'delete articles' => 'Articles',
        ];

        foreach ($permissions as $name => $tag) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['tag_name' => $tag]
            );
        }

        // Assign to Super admin role
        $superAdmin = Role::where('name', 'Super admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(array_keys($permissions));
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'view articles',
            'create articles',
            'update articles',
            'delete articles',
        ])->delete();
    }
};

<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Tests\TestCase;

class CustomerImportTemplateRoutesTest extends TestCase
{
    public function test_empty_import_template_download_requires_create_users_permission(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $this->actingAs($admin)
            ->get(route('dashboard.users.import_template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_sample_import_template_download_requires_create_users_permission(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $this->actingAs($admin)
            ->get(route('dashboard.users.import_sample_template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_sample_response_includes_sample_filename_hint(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('create users');

        $response = $this->actingAs($admin)
            ->get(route('dashboard.users.import_sample_template'))
            ->assertOk();

        $this->assertStringContainsString(
            'customers-import-sample',
            (string) $response->headers->get('content-disposition')
        );
    }
}

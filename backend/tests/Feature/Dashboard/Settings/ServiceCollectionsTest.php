<?php

namespace Tests\Feature\Dashboard\Settings;

use App\Models\User;
use App\Livewire\Dashboard\ServiceCollections\Create;
use App\Livewire\Dashboard\ServiceCollectionsTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceCollectionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.settings.index', ['tab' => 'service-collections']);
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.general_settings'));
    }

    public function test_authenticated_user_with_right_permissions_can_view_service_collections(): void
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_service_collections(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_create_service_collections(): void
    {
        $this->user->givePermissionTo('manage settings');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_service_collections(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_service_collections()
    {
        Livewire::actingAs($this->user)
            ->test(ServiceCollectionsTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_service_collections()
    {
        $this->user->givePermissionTo('manage settings');

        Livewire::actingAs($this->user)
            ->test(ServiceCollectionsTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

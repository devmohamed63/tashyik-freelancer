<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\CitiesTable;
use App\Livewire\Dashboard\Cities\Create;
use App\Livewire\Dashboard\Cities\Edit;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CitiesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected City $modelToEdit;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->modelToEdit = City::factory()->create();

        $this->indexRoute = route('dashboard.cities.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('view cities');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.cities'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_cities(): void
    {
        $this->user->givePermissionTo('view cities');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_cities(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_add_cities(): void
    {
        $this->user->givePermissionTo(['view cities', 'create cities']);

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_add_cities(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_cities()
    {
        $this->user->givePermissionTo(['view cities', 'update cities']);

        Livewire::actingAs($this->user)
            ->test(Edit::class)
            ->dispatch('edit-result', $this->modelToEdit->id)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_edit_cities(): void
    {
        Livewire::actingAs($this->user)
            ->test(Edit::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_cities()
    {
        Livewire::actingAs($this->user)
            ->test(CitiesTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_cities()
    {
        $this->user->givePermissionTo(['view cities', 'delete cities']);

        Livewire::actingAs($this->user)
            ->test(CitiesTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

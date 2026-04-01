<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Roles\Create;
use App\Livewire\Dashboard\Roles\Edit;
use App\Livewire\Dashboard\Roles\Show;
use App\Livewire\Dashboard\RolesTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Role $modelToEdit;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->modelToEdit = Role::create([
            'name' => 'test role'
        ]);

        $this->indexRoute = route('dashboard.roles.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage roles and permissions');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.roles_and_permissions'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_roles(): void
    {
        $this->user->givePermissionTo('manage roles and permissions');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_roles(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_create_roles(): void
    {
        $this->user->givePermissionTo('manage roles and permissions');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_roles(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_roles()
    {
        $this->user->givePermissionTo('manage roles and permissions');

        Livewire::actingAs($this->user)
            ->test(Edit::class)
            ->dispatch('edit-result', $this->modelToEdit->id)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_edit_roles(): void
    {
        Livewire::actingAs($this->user)
            ->test(Edit::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_show_roles()
    {
        $this->user->givePermissionTo('manage roles and permissions');

        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_roles()
    {
        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_roles()
    {
        Livewire::actingAs($this->user)
            ->test(RolesTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_roles()
    {
        $this->user->givePermissionTo('manage roles and permissions');

        Livewire::actingAs($this->user)
            ->test(RolesTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

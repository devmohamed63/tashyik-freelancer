<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Plans\Create;
use App\Livewire\Dashboard\PlansTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlansTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.plans.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage plans');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.plans'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_plans(): void
    {
        $this->user->givePermissionTo('manage plans');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_plans(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_create_plans(): void
    {
        $this->user->givePermissionTo('manage plans');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_plans(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_plans()
    {
        Livewire::actingAs($this->user)
            ->test(PlansTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_plans()
    {
        $this->user->givePermissionTo('manage plans');

        Livewire::actingAs($this->user)
            ->test(PlansTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

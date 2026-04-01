<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Orders\Show;
use App\Livewire\Dashboard\OrdersTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.orders.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage orders');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.orders'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_orders(): void
    {
        $this->user->givePermissionTo('manage orders');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_orders(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_orders()
    {
        $this->user->givePermissionTo('manage orders');

        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_orders()
    {
        Livewire::test(Show::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_orders()
    {
        Livewire::actingAs($this->user)
            ->test(OrdersTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_orders()
    {
        $this->user->givePermissionTo('manage orders');

        Livewire::actingAs($this->user)
            ->test(OrdersTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

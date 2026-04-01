<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Livewire\Dashboard\Coupons\Create;
use App\Livewire\Dashboard\CouponsTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CouponsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.coupons.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage coupons');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.coupons'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_coupons(): void
    {
        $this->user->givePermissionTo('manage coupons');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_coupons(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_create_coupons(): void
    {
        $this->user->givePermissionTo('manage coupons');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_coupons(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_coupons()
    {
        Livewire::actingAs($this->user)
            ->test(CouponsTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_coupons()
    {
        $this->user->givePermissionTo('manage coupons');

        Livewire::actingAs($this->user)
            ->test(CouponsTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

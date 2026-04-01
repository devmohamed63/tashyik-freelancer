<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Livewire\Dashboard\Promotions\Create;
use App\Livewire\Dashboard\PromotionsTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PromotionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.promotions.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage promotions');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.promotions'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_promotions(): void
    {
        $this->user->givePermissionTo('manage promotions');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_promotions(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_create_promotions(): void
    {
        $this->user->givePermissionTo('manage promotions');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_promotions(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_promotions()
    {
        Livewire::actingAs($this->user)
            ->test(PromotionsTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_promotions()
    {
        $this->user->givePermissionTo('manage promotions');

        Livewire::actingAs($this->user)
            ->test(PromotionsTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

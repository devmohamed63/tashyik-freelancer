<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Users\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.subscriptions.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage subscriptions');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.subscriptions'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_subscriptions(): void
    {
        $this->user->givePermissionTo('manage subscriptions');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_subscriptions(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_service_provider()
    {
        $this->user->givePermissionTo(['view users', 'manage subscriptions']);

        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_subscriptions()
    {
        Livewire::test(Show::class)
            ->assertStatus(403);
    }
}

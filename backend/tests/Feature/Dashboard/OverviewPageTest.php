<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverviewPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.overview');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('view dashboard');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.overview'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_overview_page(): void
    {
        $this->user->givePermissionTo('view dashboard');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_overview_page(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_view_overview_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(403);
    }
}

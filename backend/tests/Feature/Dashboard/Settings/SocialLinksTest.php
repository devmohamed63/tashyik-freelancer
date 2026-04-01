<?php

namespace Tests\Feature\Dashboard\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected $editRoute;

    protected $updateRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->editRoute = route('dashboard.settings.index', ['tab' => 'social-links']);

        $this->updateRoute = route('dashboard.settings.update_social_links');
    }

    public function test_unauthenticated_user_cannot_edit_social_links(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_edit_social_links(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_view_social_links(): void
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertSee(__('ui.update'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_with_right_permissions_can_update_social_links(): void
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'facebook_url' => fake()->url(),
                'twitter_url' => fake()->url(),
                'instagram_url' => fake()->url(),
                'snapchat_url' => fake()->url(),
                'tiktok_url' => fake()->url(),
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }
}

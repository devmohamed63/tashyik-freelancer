<?php

namespace Tests\Feature\Dashboard;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Page $modelToEdit;

    protected string $editRoute;

    protected string $updateRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->modelToEdit = Page::create([
            'name' => [
                'ar' => 'Test page',
                'en' => 'Test page',
            ],
            'body' => [
                'ar' => 'content',
                'en' => 'content',
            ],
            'tag' => 'test-page'
        ]);

        $this->editRoute = route('dashboard.settings.edit_default_page', ['page' => $this->modelToEdit->tag]);

        $this->updateRoute = route('dashboard.settings.update_default_page', ['page' => $this->modelToEdit->id]);
    }

    public function test_sidebar_links_are_displayed()
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.about-us'))
            ->assertSee(__('ui.terms-and-conditions'))
            ->assertSee(__('ui.privacy-policy'))
            ->assertSee(route('dashboard.settings.edit_default_page', ['page' => 'about-us']))
            ->assertSee(route('dashboard.settings.edit_default_page', ['page' => 'terms-and-conditions']))
            ->assertSee(route('dashboard.settings.edit_default_page', ['page' => 'privacy-policy']));
    }

    public function test_authenticated_user_with_right_permissions_can_edit_default_pages(): void
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200);

        $response = $this->put($this->updateRoute, [
            'name' => [
                'ar' => 'Test page',
            ],
            'body' => [
                'ar' => 'content',
            ],
        ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_edit_default_pages(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_edit_default_pages(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(403);
    }
}

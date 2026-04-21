<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Banners\CreateAd;
use App\Livewire\Dashboard\BannersTable;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BannersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Banner $modelToEdit;

    protected string $indexRoute;

    protected string $createRoute;

    protected string $storeRoute;

    protected string $showRoute;

    protected string $editRoute;

    protected string $updateRoute;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->user = User::factory()->create();

        $this->modelToEdit = Banner::factory()->create();

        $this->indexRoute = route('dashboard.banners.index');

        $this->createRoute = route('dashboard.banners.create');

        $this->storeRoute = route('dashboard.banners.store');

        $this->showRoute = route('dashboard.banners.show', [
            'banner' => $this->modelToEdit->id
        ]);

        $this->editRoute = route('dashboard.banners.edit', [
            'banner' => $this->modelToEdit->id
        ]);

        $this->updateRoute = route('dashboard.banners.update', [
            'banner' => $this->modelToEdit->id
        ]);
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('view banners');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.banners'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_create_banners(): void
    {
        $this->user->givePermissionTo('create banners');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200);

        $file = UploadedFile::fake()->image('image.jpg');

        $response = $this->actingAs($this->user)
            ->post($this->storeRoute, [
                'name' => [
                    'ar' => 'Test banner',
                    'en' => 'Test banner',
                ],
                'image' => $file
            ]);

        $response->assertRedirect($this->createRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_create_banners(): void
    {
        $response = $this->get($this->createRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_banners(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_banners()
    {
        $this->user->givePermissionTo('update banners');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'name' => [
                    'ar' => 'Test banner',
                    'en' => 'Test banner',
                ],
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_edit_banners(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_banners()
    {
        $this->user->givePermissionTo('view banners');

        $response = $this->actingAs($this->user)
            ->get($this->showRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_banners()
    {
        $response = $this->get($this->showRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_banners()
    {
        Livewire::actingAs($this->user)
            ->test(BannersTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_banners()
    {
        $this->user->givePermissionTo(['view banners', 'delete banners']);

        Livewire::actingAs($this->user)
            ->test(BannersTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }

    public function test_create_ad_component_accepts_guests_as_audience(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->set('audience', 'guests')
            ->set('title', 'Guest campaign')
            ->set('description', 'Promotion for guest users')
            ->call('publish')
            ->assertHasNoErrors('audience');
    }

    public function test_create_ad_component_rejects_unknown_audience(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->set('audience', 'unknown_audience')
            ->set('title', 'Invalid campaign')
            ->call('publish')
            ->assertHasErrors(['audience' => 'in']);
    }
}

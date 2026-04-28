<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Banners\CreateAd;
use App\Livewire\Dashboard\BannersTable;
use App\Models\AdBroadcast;
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
            'banner' => $this->modelToEdit->id,
        ]);

        $this->editRoute = route('dashboard.banners.edit', [
            'banner' => $this->modelToEdit->id,
        ]);

        $this->updateRoute = route('dashboard.banners.update', [
            'banner' => $this->modelToEdit->id,
        ]);
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.nav_slider_banners'))
            ->assertSee(__('ui.nav_push_notifications'))
            ->assertSee(__('ui.push_ads'))
            ->assertSee(__('ui.view_push_ads'))
            ->assertSee(__('ui.create_push_ad'))
            ->assertSee($this->indexRoute)
            ->assertSee(route('dashboard.push-ads.index'))
            ->assertSee(route('dashboard.push-ads.create'));
    }

    public function test_push_ads_index_page_is_displayed_for_authorized_users(): void
    {
        $this->user->givePermissionTo('view banners');

        $this->actingAs($this->user)
            ->get(route('dashboard.push-ads.index'))
            ->assertStatus(200)
            ->assertSee(__('ui.view_push_ads'))
            ->assertSee(__('validation.attributes.title'));
    }

    public function test_push_ads_create_page_is_displayed_for_authorized_users(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        $this->actingAs($this->user)
            ->get(route('dashboard.push-ads.create'))
            ->assertStatus(200)
            ->assertSee(__('ui.create_push_ad'))
            ->assertSee(__('ui.target_audiences'))
            ->assertSee(__('ui.publish'), false);
    }

    public function test_push_ads_create_page_returns_403_without_create_permission(): void
    {
        $this->user->givePermissionTo('view banners');

        $this->actingAs($this->user)
            ->get(route('dashboard.push-ads.create'))
            ->assertStatus(403);
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
                'image' => $file,
            ]);

        $response->assertRedirect($this->indexRoute)
            ->assertSessionHas('status');

        $this->assertSame(2, Banner::query()->count());
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

        $response->assertRedirect($this->indexRoute)
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
            ->set('audiences', ['guests'])
            ->set('title', 'Guest campaign')
            ->set('description', 'Promotion for guest users')
            ->call('publish')
            ->assertHasNoErrors('audiences');

        $this->assertDatabaseHas('ad_broadcasts', [
            'audience' => 'guests',
            'title' => 'Guest campaign',
            'user_id' => $this->user->id,
        ]);

        $this->assertSame(1, AdBroadcast::query()->count());
    }

    public function test_create_ad_renders_guests_audience_label_in_arabic(): void
    {
        $this->user->givePermissionTo(['view banners']);

        app()->setLocale('ar');

        Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->assertSee('الضيوف', false)
            ->assertDontSee('ui.guests');
    }

    public function test_create_ad_component_rejects_unknown_audience(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->set('audiences', ['unknown_audience'])
            ->set('title', 'Invalid campaign')
            ->call('publish')
            ->assertHasErrors(['audiences.0' => 'in']);
    }

    public function test_create_ad_can_publish_twice_to_same_audience(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        $component = Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->set('audiences', ['guests'])
            ->set('title', 'Repeat push')
            ->set('description', '');

        $component->call('publish')->assertHasNoErrors();

        $component
            ->set('audiences', ['guests'])
            ->set('title', 'Repeat push')
            ->set('description', '')
            ->call('publish')
            ->assertHasNoErrors();

        $this->assertSame(2, AdBroadcast::query()->where('audience', 'guests')->where('title', 'Repeat push')->count());
    }

    public function test_fill_create_ad_from_broadcast_prefills_form(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        $broadcast = AdBroadcast::query()->create([
            'audience' => 'service_providers',
            'title' => 'Stored title',
            'description' => 'Stored body',
            'image_path' => 'ads/example.png',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->call('fillCreateAdFromBroadcast', $broadcast->id)
            ->assertSet('audiences', ['service_providers'])
            ->assertSet('title', 'Stored title')
            ->assertSet('description', 'Stored body')
            ->assertSet('resendStoragePath', 'ads/example.png');
    }

    public function test_create_ad_accepts_multiple_audiences_in_one_broadcast(): void
    {
        $this->user->givePermissionTo(['view banners', 'create banners']);

        Livewire::actingAs($this->user)
            ->test(CreateAd::class)
            ->set('audiences', ['customers', 'guests'])
            ->set('title', 'Multi audience')
            ->set('description', '')
            ->call('publish')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ad_broadcasts', [
            'audience' => 'customers,guests',
            'title' => 'Multi audience',
            'user_id' => $this->user->id,
        ]);
    }
}

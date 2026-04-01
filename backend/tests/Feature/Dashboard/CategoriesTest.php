<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\CategoriesTable;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $modelToEdit;

    protected string $indexRoute;

    protected string $subcategoriesRoute;

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

        $this->modelToEdit = Category::factory()->create();

        $this->indexRoute = route('dashboard.categories.index');

        $this->subcategoriesRoute = route('dashboard.categories.children');

        $this->createRoute = route('dashboard.categories.create');

        $this->storeRoute = route('dashboard.categories.store');

        $this->showRoute = route('dashboard.categories.show', [
            'category' => $this->modelToEdit->id
        ]);

        $this->editRoute = route('dashboard.categories.edit', [
            'category' => $this->modelToEdit->id
        ]);

        $this->updateRoute = route('dashboard.categories.update', [
            'category' => $this->modelToEdit->id
        ]);
    }

    public function test_create_categories_link_is_displayed()
    {
        $this->user->givePermissionTo('create categories');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.add_categories'))
            ->assertSee($this->createRoute);
    }

    public function test_view_categories_link_is_displayed()
    {
        $this->user->givePermissionTo('view categories');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.view_categories'))
            ->assertSee($this->indexRoute);
    }

    public function test_view_subcategories_link_is_displayed()
    {
        $this->user->givePermissionTo('view categories');

        $response = $this->actingAs($this->user)
            ->get($this->subcategoriesRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.view_subcategories'))
            ->assertSee($this->subcategoriesRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_create_categories(): void
    {
        $this->user->givePermissionTo('create categories');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200);

        $file = UploadedFile::fake()->image('image.jpg');

        $response = $this->actingAs($this->user)
            ->post($this->storeRoute, [
                'name' => [
                    'ar' => 'test',
                ],
                'image' => $file,
                'parent' => Category::factory()->create()->id
            ]);

        $response->assertRedirect($this->createRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_create_categories(): void
    {
        $response = $this->get($this->createRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_categories(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_categories()
    {
        $this->user->givePermissionTo('update categories');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200);

        $cities = City::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'name' => [
                    'ar' => 'test',
                ],
                'cities' => $cities->pluck('id')->toArray()
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_edit_categories(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_categories()
    {
        $this->user->givePermissionTo('view categories');

        $response = $this->actingAs($this->user)
            ->get($this->showRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_categories()
    {
        $response = $this->get($this->showRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_categories()
    {
        Livewire::actingAs($this->user)
            ->test(CategoriesTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_categories()
    {
        $this->user->givePermissionTo(['view categories', 'delete categories']);

        Livewire::actingAs($this->user)
            ->test(CategoriesTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

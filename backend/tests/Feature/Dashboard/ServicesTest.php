<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\ServicesTable;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Service $modelToEdit;

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

        $category = Category::factory()->create();

        $this->modelToEdit = Service::factory()->create([
            'category_id' => $category->id
        ]);

        $this->indexRoute = route('dashboard.services.index');

        $this->createRoute = route('dashboard.services.create');

        $this->storeRoute = route('dashboard.services.store');

        $this->showRoute = route('dashboard.services.show', [
            'service' => $this->modelToEdit->id
        ]);

        $this->editRoute = route('dashboard.services.edit', [
            'service' => $this->modelToEdit->id
        ]);

        $this->updateRoute = route('dashboard.services.update', [
            'service' => $this->modelToEdit->id
        ]);
    }

    public function test_create_services_link_is_displayed()
    {
        $this->user->givePermissionTo('create services');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.add_services'))
            ->assertSee($this->createRoute);
    }

    public function test_view_services_link_is_displayed()
    {
        $this->user->givePermissionTo('view services');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.view_services'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_create_services(): void
    {
        $this->user->givePermissionTo('create services');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200);

        $file = UploadedFile::fake()->image('image.jpg');

        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->post($this->storeRoute, [
                'name' => [
                    'ar' => 'test',
                ],
                'description' => [
                    'ar' => 'test',
                ],
                'category' => $category->id,
                'highlights' => [
                    fake()->word(),
                    fake()->word(),
                ],
                'price' => 20,
                'image' => $file,
            ]);

        $response->assertRedirect($this->createRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_create_services(): void
    {
        $response = $this->get($this->createRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_services(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_services()
    {
        $this->user->givePermissionTo('update services');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200);

        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'name' => [
                    'ar' => 'test',
                ],
                'description' => [
                    'ar' => 'test',
                ],
                'category' => $category->id,
                'highlights' => [
                    fake()->word(),
                    fake()->word(),
                ],
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_edit_services(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_services()
    {
        $this->user->givePermissionTo('view services');

        $response = $this->actingAs($this->user)
            ->get($this->showRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_services()
    {
        $response = $this->get($this->showRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_services()
    {
        Livewire::actingAs($this->user)
            ->test(ServicesTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_services()
    {
        $this->user->givePermissionTo(['view services', 'delete services']);

        Livewire::actingAs($this->user)
            ->test(ServicesTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

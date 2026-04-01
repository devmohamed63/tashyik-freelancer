<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\pagesTable;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Page $modelToEdit;

    protected string $indexRoute;

    protected string $createRoute;

    protected string $storeRoute;

    protected string $editRoute;

    protected string $updateRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->modelToEdit = Page::factory()->create();

        $this->indexRoute = route('dashboard.pages.index');

        $this->createRoute = route('dashboard.pages.create');

        $this->storeRoute = route('dashboard.pages.store');

        $this->editRoute = route('dashboard.pages.edit', [
            'page' => $this->modelToEdit->id
        ]);

        $this->updateRoute = route('dashboard.pages.update', [
            'page' => $this->modelToEdit->id
        ]);
    }

    // public function test_create_pages_link_is_displayed()
    // {
    //     $this->user->givePermissionTo('create pages');

    //     $response = $this->actingAs($this->user)
    //         ->get($this->createRoute);

    //     $response->assertStatus(200)
    //         ->assertSee(__('ui.add_pages'))
    //         ->assertSee($this->createRoute);
    // }

    public function test_view_pages_link_is_displayed()
    {
        $this->user->givePermissionTo('view pages');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);

        // $response->assertStatus(200)
        //     ->assertSee(__('ui.view_pages'))
        //     ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_create_pages(): void
    {
        $this->user->givePermissionTo('create pages');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->post($this->storeRoute, [
                'name' => [
                    'ar' => 'Test page',
                    'en' => 'Test page',
                ],
                'body' => [
                    'ar' => 'contect',
                    'en' => 'contect',
                ],
            ]);

        $response->assertRedirect($this->createRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_create_pages(): void
    {
        $response = $this->get($this->createRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_pages(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_pages()
    {
        $this->user->givePermissionTo('update pages');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'name' => [
                    'ar' => 'Test page',
                    'en' => 'Test page',
                ],
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_edit_pages(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_pages()
    {
        Livewire::actingAs($this->user)
            ->test(PagesTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_pages()
    {
        $this->user->givePermissionTo(['view pages', 'delete pages']);

        Livewire::actingAs($this->user)
            ->test(PagesTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

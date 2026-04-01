<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Livewire\Dashboard\Users\Create;
use App\Livewire\Dashboard\Users\Show;
use App\Livewire\Dashboard\UsersTable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $modelToEdit;

    protected string $indexRoute;

    protected string $serviceProvidersRoute;

    protected string $payoutRequestsRoute;

    protected string $createRoute;

    protected string $storeRoute;

    protected string $editRoute;

    protected string $updateRoute;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->user = User::factory()->create();

        $this->modelToEdit = User::factory()->create();

        $this->indexRoute = route('dashboard.users.index');

        $this->serviceProvidersRoute = route('dashboard.users.service_providers');

        $this->payoutRequestsRoute = route('dashboard.users.payout_requests');

        $this->createRoute = route('dashboard.users.create');

        $this->storeRoute = route('dashboard.users.store');

        $this->editRoute = route('dashboard.users.edit', [
            'user' => $this->modelToEdit->id
        ]);

        $this->updateRoute = route('dashboard.users.update', [
            'user' => $this->modelToEdit->id
        ]);
    }

    public function test_add_users_link_is_displayed()
    {
        $this->user->givePermissionTo('create users');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.add_users'))
            ->assertSee($this->createRoute);
    }

    public function test_view_users_link_is_displayed()
    {
        $this->user->givePermissionTo('view users');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.view_users'))
            ->assertSee($this->indexRoute);
    }

    public function test_view_service_providers_link_is_displayed()
    {
        $this->user->givePermissionTo('view users');

        $response = $this->actingAs($this->user)
            ->get($this->serviceProvidersRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.view_service_providers'))
            ->assertSee($this->serviceProvidersRoute);
    }

    public function test_view_payout_requests_link_is_displayed()
    {
        $this->user->givePermissionTo('view users');

        $response = $this->actingAs($this->user)
            ->get($this->payoutRequestsRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.view_payout_requests'))
            ->assertSee($this->payoutRequestsRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_create_users(): void
    {
        $this->user->givePermissionTo('create users');

        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(200);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->user)
            ->post($this->storeRoute, [
                'name' => 'Test User',
                'phone' => '0537507076',
                'password' => 'password',
                'image' => $file
            ]);

        $response->assertRedirect($this->createRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_create_users(): void
    {
        $response = $this->get($this->createRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_create_users(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->createRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_edit_users()
    {
        $this->user->givePermissionTo('update users');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'name' => 'Test User',
                'phone' => '0537507076',
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }

    public function test_unauthenticated_user_cannot_edit_users(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_users()
    {
        $this->user->givePermissionTo('view users');

        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_users()
    {
        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_add_service_providers(): void
    {
        $this->user->givePermissionTo('view users');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_add_service_providers(): void
    {
        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_users()
    {
        Livewire::actingAs($this->user)
            ->test(UsersTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_users()
    {
        $this->user->givePermissionTo(['view users', 'delete users']);

        Livewire::actingAs($this->user)
            ->test(UsersTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

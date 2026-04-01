<?php

namespace Tests\Feature\Dashboard;

use App\Livewire\Dashboard\Contacts\Show;
use App\Livewire\Dashboard\ContactsTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->indexRoute = route('dashboard.contacts.index');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage contact requests');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.contact_requests'))
            ->assertSee($this->indexRoute);
    }

    public function test_authenticated_user_with_right_permissions_can_view_contact_requests(): void
    {
        $this->user->givePermissionTo('manage contact requests');

        $response = $this->actingAs($this->user)
            ->get($this->indexRoute);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_view_contact_requests(): void
    {
        $response = $this->get($this->indexRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_right_permissions_can_show_contact_requests()
    {
        $this->user->givePermissionTo('manage contact requests');

        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_show_contact_requests()
    {
        Livewire::actingAs($this->user)
            ->test(Show::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_delete_contact_requests()
    {
        Livewire::actingAs($this->user)
            ->test(ContactsTable::class)
            ->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_delete_contact_requests()
    {
        $this->user->givePermissionTo('manage contact requests');

        Livewire::actingAs($this->user)
            ->test(ContactsTable::class)
            ->call('delete', [1, 2])
            ->assertDispatched('showModal', ['id' => 'deleteConfirmationModal'])
            ->call('confirmDelete')
            ->assertDispatched('hideModal', ['id' => 'deleteConfirmationModal']);
    }
}

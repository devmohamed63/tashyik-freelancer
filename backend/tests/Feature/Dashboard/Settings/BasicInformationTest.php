<?php

namespace Tests\Feature\Dashboard\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class BasicInformationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected $editRoute;

    protected $updateRoute;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->user = User::factory()->create();

        $this->editRoute = route('dashboard.settings.index', ['tab' => 'basic-information']);

        $this->updateRoute = route('dashboard.settings.update_basic_information');
    }

    public function test_sidebar_link_is_displayed()
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(200)
            ->assertSee(__('ui.general_settings'))
            ->assertSee($this->editRoute);
    }

    public function test_unauthenticated_user_cannot_edit_basic_information(): void
    {
        $response = $this->get($this->editRoute);

        $response->assertStatus(302);
    }

    public function test_authenticated_user_with_wrong_permissions_cannot_edit_basic_information(): void
    {
        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_with_right_permissions_can_view_basic_information(): void
    {
        $this->user->givePermissionTo('manage settings');

        $response = $this->actingAs($this->user)
            ->get($this->editRoute);

        $response->assertSee(__('ui.update'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_with_right_permissions_can_update_basic_information(): void
    {
        $this->user->givePermissionTo('manage settings');

        $icon = UploadedFile::fake()->image('icon.png');

        $response = $this->actingAs($this->user)
            ->put($this->updateRoute, [
                'icon' => $icon,
                'name' => [
                    'ar' => 'test',
                ],
                'description' => [
                    'ar' => 'test',
                ],
                'phone_number' => 'test',
                'whatsapp_link' => 'test',
                'email' => 'test',
            ]);

        $response->assertRedirect($this->editRoute)
            ->assertSessionHas('status');
    }
}

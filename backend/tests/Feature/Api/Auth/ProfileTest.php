<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->user = User::factory()->create([
            'password' => 'password'
        ]);
    }

    public function test_user_can_update_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('api.profile.update'), [
                'name' => fake()->name(),
                'phone' => '0537507076',
                'image' => UploadedFile::fake()->image('image1.jpg'),
                'national_address_image' => UploadedFile::fake()->image('image2.jpg'),
                'tax_registration_number' => 'HHJ37717GALL222',
            ]);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')
                    ->etc()
            );
    }

    public function test_user_can_update_password(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson(route('api.profile.update_password'), [
                'current_password' => 'password',
                'password' => '20202020220',
                'password_confirmation' => '20202020220',
            ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_password_with_wrong_credentials(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson(route('api.profile.update_password'), [
                'current_password' => 'abc',
                'password' => '20202020220',
                'password_confirmation' => '2222',
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_delete_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->delete(route('api.profile.delete'));

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Api\General;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateFCMTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_fcm_token_can_updated(): void
    {
        /**
         * @var User
         */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('api.general.update_fcm_token'), [
                'token' => fake()->text(40),
                'ui_locale' => 'ar',
            ]);

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Api\General;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateUserLocationTest extends TestCase
{
    public function test_user_location_can_updated(): void
    {
        /**
         * @var User
         */
        $user = User::factory()->create();

        $response = $this->actingAs($user);

        $response = $this->putJson(route('api.general.update_user_location'), [
            'latitude' => '29.3084',
            'longitude' => '30.8428',
        ]);

        $response->assertStatus(200);
    }
}

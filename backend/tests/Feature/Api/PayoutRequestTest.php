<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_provider_can_request_payout(): void
    {
        /**
         * @var User
         */
        $serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE
        ]);

        $route = route('api.request_payout');

        $response = $this->actingAs($serviceProvider)
            ->getJson($route);

        $response->assertStatus(200);
    }
}

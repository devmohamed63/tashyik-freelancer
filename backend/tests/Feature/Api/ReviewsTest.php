<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_service_provider(): void
    {
        $route = route('api.reviews.store');

        /**
         * @var User
         */
        $user = User::factory()->create();

        $serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
        ]);

        $response = $this->actingAs($user)
            ->postJson($route, [
                'service_provider' => $serviceProvider->id,
                'rating' => 5,
                'body' => 'Good service',
            ]);

        $response->assertStatus(201);
    }
}

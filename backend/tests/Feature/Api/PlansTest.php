<?php

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PlansTest extends TestCase
{
    use RefreshDatabase;

    protected User $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
        ]);
    }

    public function test_subscription_info_is_displayed_to_service_provider(): void
    {
        $route = route('api.plans.index');

        $response = $this->actingAs($this->serviceProvider)
            ->getJson($route);

        $response->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('subscription.starts_at')
                ->has('subscription.ends_at')
                ->etc()
        );
    }

    public function test_plans_are_displayed_to_service_provider(): void
    {
        $route = route('api.plans.index');

        Plan::factory(3)->create([
            'target_group' => $this->serviceProvider->entity_type,
        ]);

        $response = $this->actingAs($this->serviceProvider)
            ->getJson($route);

        $response->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('plans.0.id')
                ->etc()
        );
    }

    public function test_single_plan_is_displayed_to_service_provider(): void
    {
        $plan = Plan::factory()->create();

        $route = route('api.plans.show', [
            'plan' => $plan->id,
            'confirm_order' => false,
        ]);

        $response = $this->actingAs($this->serviceProvider)
            ->getJson($route);

        $response->assertStatus(200)
            ->assertSee(['price' => $plan->price]);
    }
}

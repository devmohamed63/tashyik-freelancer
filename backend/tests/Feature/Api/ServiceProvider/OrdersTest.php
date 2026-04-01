<?php

namespace Tests\Feature\Api\ServiceProvider;

use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected User $serviceProvider;

    protected string $latitude;

    protected string $longitude;

    protected function setUp(): void
    {
        parent::setUp();

        $this->latitude = fake()->latitude();
        $this->longitude = fake()->longitude();

        $this->serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        $this->category = Category::factory()->create();

        $this->serviceProvider->categories()->attach($this->category->id);
    }

    public function test_new_orders_are_displayed_to_service_provider(): void
    {
        $route = route('api.service_provider.orders.index', [
            'status' => 'new'
        ]);

        Order::factory()->create([
            'service_provider_id' => null,
            'category_id' => $this->category->id,
            'status' => Order::NEW_STATUS,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        $this->actingAs($this->serviceProvider)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_completed_orders_are_displayed_to_service_provider(): void
    {
        Order::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $route = route('api.service_provider.orders.index', [
            'status' => 'completed'
        ]);

        $this->actingAs($this->serviceProvider)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_single_order_is_displayed_to_service_provider(): void
    {
        $order = Order::factory()->create([
            'customer_id' => User::factory()->create()->id,
            'service_provider_id' => $this->serviceProvider->id,
        ]);

        $route = route('api.service_provider.orders.show', ['order' => $order->id]);

        $this->actingAs($this->serviceProvider)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('id')
                    ->etc()
            );
    }

    public function test_service_provider_with_active_subscription_can_accept_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => User::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'service_provider_id' => null,
            'status' => Order::NEW_STATUS,
        ]);

        $route = route('api.service_provider.orders.update', [
            'order' => $order->id,
            'status' => Order::SERVICE_PROVIDER_ON_THE_WAY,
        ]);

        $this->serviceProvider->subscription()->updateOrCreate([
            'ends_at' => now()->addDays(2),
        ]);

        $this->actingAs($this->serviceProvider)
            ->putJson($route)
            ->assertStatus(200);
    }

    public function test_service_provider_with_expired_subscription_cannot_accept_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => User::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'service_provider_id' => null,
            'status' => Order::NEW_STATUS,
        ]);

        $route = route('api.service_provider.orders.update', [
            'order' => $order->id,
            'status' => Order::SERVICE_PROVIDER_ON_THE_WAY,
        ]);

        $this->serviceProvider->subscription()->updateOrCreate([
            'ends_at' => now()->subDays(2),
        ]);

        $this->actingAs($this->serviceProvider)
            ->putJson($route)
            ->assertStatus(402);
    }

    public function test_service_provider_can_complete_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => User::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'service_provider_id' => $this->serviceProvider->id,
            'status' => Order::STARTED_STATUS,
        ]);

        $route = route('api.service_provider.orders.update', [
            'order' => $order->id,
            'status' => Order::COMPLETED_STATUS,
            'notes' => 'test'
        ]);

        $this->actingAs($this->serviceProvider)
            ->putJson($route)
            ->assertStatus(200);
    }

    public function test_service_provider_can_ignore_orders(): void
    {
        $order = Order::factory()->create();

        $route = route('api.service_provider.orders.destroy', [
            'order' => $order->id,
        ]);

        $this->actingAs($this->serviceProvider)
            ->deleteJson($route)
            ->assertStatus(200);
    }
}

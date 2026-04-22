<?php

namespace Tests\Feature\Api\User;

use App\Models\Address;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Utils\Traits\HasTax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use HasTax, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'balance' => 100,
        ]);
    }

    public function test_user_can_make_orders(): void
    {
        $route = route('api.user.orders.store');

        $tax = $this->getTaxes(100);

        $basicData = [
            'address' => Address::factory()->create(['user_id' => $this->user->id])->id,
            'service' => Service::factory()->create(['price' => 50 - ($tax / 2)])->id,
            'quantity' => 2,
        ];

        $this->actingAs($this->user)
            ->postJson($route, [...$basicData, ...['confirm_order' => false]])
            ->assertStatus(200)
            ->assertSee(['subtotal' => 100 - $tax])
            ->assertSee(['wallet_balance' => 100 - $tax])
            ->assertSee(['total' => 0]);

        $this->actingAs($this->user)
            ->postJson($route, [...$basicData, ...['confirm_order' => true]])
            ->assertStatus(201);
    }

    public function test_user_can_make_orders_using_service_slug(): void
    {
        $route = route('api.user.orders.store');

        $tax = $this->getTaxes(100);

        $service = Service::factory()->create([
            'price' => 50 - ($tax / 2),
            'slug'  => 'ac-cleaning-service',
        ]);

        $payload = [
            'address'       => Address::factory()->create(['user_id' => $this->user->id])->id,
            'service'       => $service->slug,
            'quantity'      => 2,
            'confirm_order' => false,
        ];

        $this->actingAs($this->user)
            ->postJson($route, $payload)
            ->assertStatus(200)
            ->assertSee(['subtotal' => 100 - $tax]);
    }

    public function test_order_fails_when_service_does_not_exist(): void
    {
        $route = route('api.user.orders.store');

        $payload = [
            'address'       => Address::factory()->create(['user_id' => $this->user->id])->id,
            'service'       => 'non-existing-slug',
            'quantity'      => 1,
            'confirm_order' => false,
        ];

        $this->actingAs($this->user)
            ->postJson($route, $payload)
            ->assertStatus(422)
            ->assertJsonPath('errors.errors.service.0', __('validation.exists', ['attribute' => 'service']));

        $this->actingAs($this->user)
            ->postJson($route, [...$payload, 'service' => 999999])
            ->assertStatus(422)
            ->assertJsonPath('errors.errors.service.0', __('validation.exists', ['attribute' => 'service']));
    }

    public function test_new_orders_are_displayed_to_user(): void
    {
        $route = route('api.user.orders.index', [
            'status' => 'new'
        ]);

        Order::factory()->create([
            'customer_id' => $this->user->id,
            'status' => Order::NEW_STATUS,
        ]);

        $this->actingAs($this->user)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_in_progress_orders_are_displayed_to_user(): void
    {
        $route = route('api.user.orders.index', [
            'status' => 'in_progress'
        ]);

        Order::factory()->create([
            'customer_id' => $this->user->id,
            'status' => Order::SERVICE_PROVIDER_ARRIVED,
        ]);

        $this->actingAs($this->user)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_completed_orders_are_displayed_to_user(): void
    {
        $route = route('api.user.orders.index', [
            'status' => 'completed'
        ]);

        Order::factory()->create([
            'customer_id' => $this->user->id,
            'service_provider_id' => User::factory()->create()->id,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $this->actingAs($this->user)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_single_order_is_displayed_to_user(): void
    {
        $order = Order::factory()->create(['customer_id' => $this->user->id]);

        $route = route('api.user.orders.show', ['order' => $order->id]);

        $this->actingAs($this->user)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('id')
                    ->etc()
            );
    }

    public function test_user_cannot_cancel_order_before_waiting_time(): void
    {
        $order = Order::factory()->create([
            'status' => Order::NEW_STATUS,
            'created_at' => now(),
            'customer_id' => $this->user->id,
        ]);

        $route = route('api.user.orders.destroy', ['order' => $order->id]);

        $this->actingAs($this->user)
            ->deleteJson($route)
            ->assertStatus(403);
    }

    public function test_user_can_cancel_order_after_waiting_time(): void
    {
        $order = Order::factory()->create([
            'status' => Order::NEW_STATUS,
            'created_at' => now()->subMinutes(Order::CANCEL_WAITING_TIME),
            'customer_id' => $this->user->id,
        ]);

        $route = route('api.user.orders.destroy', ['order' => $order->id]);

        $this->actingAs($this->user)
            ->deleteJson($route)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Api\ServiceProvider;

use App\Events\NewOrderExtra;
use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\OrderExtra;
use App\Utils\Traits\HasTax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class OrderExtrasTest extends TestCase
{
    use HasTax, RefreshDatabase;

    protected User $serviceProvider;

    protected Order $order;

    protected OrderExtra $orderExtra;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
        ]);

        // Create a service for the order extra
        Service::factory()->create();

        $this->order = Order::factory()->create();

        $this->orderExtra = OrderExtra::factory()->create([
            'order_id' => $this->order->id,
            'status' => OrderExtra::PAID_STATUS,
            'wallet_balance' => 200,
            'total' => 50,
        ]);
    }

    public function test_order_extras_are_displayed_to_service_provider(): void
    {
        $route = route('api.service_provider.order-extra.index', ['order' => $this->order->id]);

        $this->actingAs($this->serviceProvider)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_service_provider_can_create_order_extra(): void
    {
        $route = route('api.service_provider.order-extra.store', [
            'order' => Order::factory()->create()->id,
            'service' => Service::factory()->create()->id,
            'quantity' => 2,
            'materials' => 70,
        ]);

        $this->actingAs($this->serviceProvider)
            ->getJson($route)
            ->assertStatus(200);
    }

    public function test_service_provider_can_create_order_extra_using_slug(): void
    {
        Event::fake([NewOrderExtra::class]);

        $service = Service::factory()->create(['slug' => 'extra-ac-check']);
        $order   = Order::factory()->create();

        $route = route('api.service_provider.order-extra.store');

        $this->actingAs($this->serviceProvider)
            ->postJson($route, [
                'order'     => $order->id,
                'service'   => $service->slug,
                'quantity'  => 1,
                'materials' => 0,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('order_extras', [
            'order_id'   => $order->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_service_provider_can_create_order_extra_using_id(): void
    {
        Event::fake([NewOrderExtra::class]);

        $service = Service::factory()->create();
        $order   = Order::factory()->create();

        $route = route('api.service_provider.order-extra.store');

        $this->actingAs($this->serviceProvider)
            ->postJson($route, [
                'order'     => $order->id,
                'service'   => $service->id,
                'quantity'  => 1,
                'materials' => 0,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('order_extras', [
            'order_id'   => $order->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_order_extra_fails_when_service_does_not_exist(): void
    {
        $route = route('api.service_provider.order-extra.store');

        $this->actingAs($this->serviceProvider)
            ->postJson($route, [
                'order'    => Order::factory()->create()->id,
                'service'  => 'non-existing-slug',
                'quantity' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.errors.service.0', __('validation.exists', ['attribute' => 'service']));

        $this->actingAs($this->serviceProvider)
            ->postJson($route, [
                'order'    => Order::factory()->create()->id,
                'service'  => 999999,
                'quantity' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.errors.service.0', __('validation.exists', ['attribute' => 'service']));
    }
}

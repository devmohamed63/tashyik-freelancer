<?php

namespace Tests\Feature\Api\ServiceProvider;

use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\OrderExtra;
use App\Utils\Traits\HasTax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}

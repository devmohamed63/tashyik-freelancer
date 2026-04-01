<?php

namespace Tests\Feature\Api\User;

use App\Models\Order;
use App\Models\OrderExtra;
use App\Models\Service;
use App\Models\User;
use App\Utils\Traits\HasTax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class OrderExtrasTest extends TestCase
{
    use HasTax, RefreshDatabase;

    protected User $user;

    protected Order $order;

    protected OrderExtra $orderExtra;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

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

    public function test_order_extras_are_displayed_to_user(): void
    {
        $route = route('api.user.order-extra.index', ['order' => $this->order->id]);

        $this->actingAs($this->user)
            ->getJson($route)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0')
                    ->etc()
            );
    }

    public function test_single_order_extra_can_displayed_to_user(): void
    {
        $route = route('api.user.order-extra.show', [
            'order_extra' => $this->orderExtra->id,
            'confirm_order' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson($route)
            ->assertStatus(200)
            ->assertSee(['wallet_balance' => $this->orderExtra->wallet_balance])
            ->assertSee(['total' => $this->orderExtra->total]);
    }
}

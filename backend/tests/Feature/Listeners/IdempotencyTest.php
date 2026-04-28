<?php

namespace Tests\Feature\Listeners;

use App\Events\NewBankTransfer;
use App\Events\OrderCompleted;
use App\Events\PlanPaid;
use App\Listeners\CreateBankTransferInvoice;
use App\Listeners\CreateOrderExtraInvoice;
use App\Listeners\CreateOrderInvoice;
use App\Listeners\CreateSubscriptionInvoice;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderExtra;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Utils\Services\Daftra;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IdempotencyTest extends TestCase
{
    public function test_create_order_invoice_is_idempotent_per_order_event(): void
    {
        Queue::fake();

        $customer = User::factory()->create(['type' => User::USER_ACCOUNT_TYPE]);
        $serviceProvider = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'service_provider_id' => $serviceProvider->id,
            'service_id' => $service->id,
            'category_id' => $category->id,
            'subtotal' => 200,
            'tax' => 30,
            'total' => 230,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $listener = new CreateOrderInvoice;
        $event = new OrderCompleted($order);

        $listener->handle($event);
        $listener->handle($event);

        $this->assertEquals(
            1,
            Invoice::where('event_uid', "order:{$order->id}:type:".Invoice::COMPLETED_ORDER_TYPE)->count()
        );
        $this->assertEquals(
            1,
            Invoice::where('event_uid', "order:{$order->id}:type:".Invoice::COMPLETED_ORDER_TAX_TYPE)->count()
        );
    }

    public function test_create_order_extra_invoice_is_idempotent_per_extra_event(): void
    {
        Queue::fake();
        Http::fake();

        $customer = User::factory()->create(['type' => User::USER_ACCOUNT_TYPE]);
        $serviceProvider = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'service_provider_id' => $serviceProvider->id,
            'service_id' => $service->id,
            'category_id' => $category->id,
            'subtotal' => 200,
            'tax' => 30,
            'total' => 230,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $extra = OrderExtra::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'status' => OrderExtra::PAID_STATUS,
            'price' => 100,
            'materials' => 25,
            'tax' => 18.75,
            'total' => 123.75,
        ]);

        $listener = new CreateOrderExtraInvoice;
        $event = new OrderCompleted($order);

        $listener->handle($event);
        $listener->handle($event);

        $this->assertEquals(
            1,
            Invoice::where('event_uid', "order_extra:{$extra->id}:type:".Invoice::ADDITIONAL_SERVICES_TYPE)->count()
        );
        $this->assertEquals(
            1,
            Invoice::where('event_uid', "order_extra:{$extra->id}:type:".Invoice::ADDITIONAL_SERVICES_TAX_TYPE)->count()
        );
    }

    public function test_create_order_extra_invoice_handles_materials_only_case(): void
    {
        Queue::fake();
        Http::fake();

        $customer = User::factory()->create(['type' => User::USER_ACCOUNT_TYPE]);
        $serviceProvider = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $category = Category::factory()->create();
        $service = Service::factory()->create(['category_id' => $category->id]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'service_provider_id' => $serviceProvider->id,
            'service_id' => $service->id,
            'category_id' => $category->id,
            'subtotal' => 200,
            'tax' => 30,
            'total' => 230,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $extra = OrderExtra::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'status' => OrderExtra::PAID_STATUS,
            'price' => 0,
            'materials' => 40,
            'tax' => 6,
            'total' => 46,
        ]);

        $listener = new CreateOrderExtraInvoice;
        $event = new OrderCompleted($order);

        $listener->handle($event);
        $listener->handle($event);

        $this->assertEquals(
            1,
            Invoice::where('event_uid', "order_extra:{$extra->id}:type:".Invoice::ADDITIONAL_SERVICES_TYPE)->count()
        );
    }

    public function test_create_subscription_invoice_is_idempotent_per_plan_paid_event(): void
    {
        Queue::fake();

        $serviceProvider = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $plan = Plan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $serviceProvider->id,
            'plan_id' => $plan->id,
            'paid_amount' => 115,
        ]);

        $eventData = [
            'service_provider_id' => $serviceProvider->id,
            'paid_amount' => 115,
            'wallet_balance' => 0,
            'transaction_id' => 'txn-123',
        ];

        $listener = new CreateSubscriptionInvoice;
        $event = new PlanPaid($eventData);

        $listener->handle($event);
        $listener->handle($event);

        $this->assertEquals(
            1,
            Invoice::where('event_uid', 'plan_paid:txn-123:type:'.Invoice::RENEW_SUBSCRIPTION_TYPE)->count()
        );
        $this->assertEquals(
            1,
            Invoice::where('event_uid', 'plan_paid:txn-123:type:'.Invoice::RENEW_SUBSCRIPTION_TAX_TYPE)->count()
        );
    }

    public function test_create_subscription_invoice_skips_when_paid_amount_is_zero(): void
    {
        Queue::fake();

        $serviceProvider = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);

        $listener = new CreateSubscriptionInvoice;
        $event = new PlanPaid([
            'service_provider_id' => $serviceProvider->id,
            'paid_amount' => 0,
            'wallet_balance' => 0,
            'transaction_id' => 'txn-zero',
        ]);

        $listener->handle($event);

        $this->assertEquals(
            0,
            Invoice::where('event_uid', 'plan_paid:txn-zero:type:'.Invoice::RENEW_SUBSCRIPTION_TYPE)->count()
        );
    }

    public function test_create_bank_transfer_invoice_is_idempotent_per_payout_request(): void
    {
        $serviceProvider = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);

        $listener = new CreateBankTransferInvoice;
        $event = new NewBankTransfer($serviceProvider, 150.0, 77);

        $listener->handle($event, app(Daftra::class));
        $listener->handle($event, app(Daftra::class));

        $this->assertEquals(
            1,
            Invoice::where('event_uid', 'payout:'.$serviceProvider->id.':request:77:type:'.Invoice::BANK_TRANSFER_TYPE)->count()
        );
    }
}

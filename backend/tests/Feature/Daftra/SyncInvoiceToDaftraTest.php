<?php

namespace Tests\Feature\Daftra;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Plan;
use App\Jobs\SyncInvoiceToDaftra;
use App\Utils\Services\Daftra;
use App\Utils\Services\Daftra\DTOs\InvoiceDTO;
use App\Utils\Services\Daftra\DTOs\PaymentDTO;
use App\Utils\Services\Daftra\DTOs\CreditNoteDTO;
use App\Jobs\SyncCreditNoteToDaftra;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class SyncInvoiceToDaftraTest extends TestCase
{
    private User $customer;
    private User $serviceProvider;
    private Service $service;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up Daftra config for all tests
        config([
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.cost_center_id' => 1,
            'services.daftra.bank_account_id' => 2,
            'services.daftra.revenue_account_id' => 28,
            'services.daftra.return_account_id' => 368,
            'app.tax_rate' => 15,
        ]);

        // Create base models
        $this->customer = User::factory()->create([
            'type' => User::USER_ACCOUNT_TYPE,
            'name' => 'عميل تجريبي',
            'email' => 'customer@test.com',
            'phone' => '0500000001',
        ]);

        $this->serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'entity_type' => User::INDIVIDUAL_ENTITY_TYPE,
            'name' => 'فني تجريبي',
            'email' => 'sp@test.com',
            'phone' => '0500000002',
        ]);

        $this->category = Category::factory()->create();
        $this->service = Service::factory()->create([
            'category_id' => $this->category->id,
            'price' => 200,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // 1. IDEMPOTENCY — لا يُرسل فاتورة مُزامنة مسبقاً
    // ─────────────────────────────────────────────────────────

    public function test_skips_already_synced_invoice(): void
    {
        Http::fake(); // Should not be called

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 230,
            'daftra_id' => 999, // Already synced!
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        Http::assertNothingSent();
    }

    // ─────────────────────────────────────────────────────────
    // 2. TYPE FILTERING — يتجاهل الأنواع غير المدعومة
    // ─────────────────────────────────────────────────────────

    public function test_skips_unsupported_invoice_types(): void
    {
        Http::fake();

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'type' => Invoice::ADDITIONAL_SERVICES_TYPE, // Not synced
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 100,
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        Http::assertNothingSent();
    }

    // ─────────────────────────────────────────────────────────
    // 3. ORDER INVOICE — التدفق المالي الكامل للطلبات
    // ─────────────────────────────────────────────────────────

    public function test_order_invoice_sends_correct_financial_data(): void
    {
        // Simulate: service 200 * qty 2 + visit 50 = subtotal 450
        // tax = 450 * 15% = 67.50
        // coupons_total = 30 (discount)
        // amount = subtotal + tax = 517.50

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'service_provider_id' => $this->serviceProvider->id,
            'service_id' => $this->service->id,
            'category_id' => $this->category->id,
            'quantity' => 2,
            'visit_cost' => 50,
            'subtotal' => 450,
            'tax_rate' => 15,
            'tax' => 67.50,
            'coupons_total' => 30,
            'wallet_balance' => 0,
            'total' => 487.50,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'target_id' => $order->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 517.50, // subtotal + tax
        ]);

        // Fake Daftra API responses
        Http::fake([
            '*/api2/clients' => Http::response(['Client' => ['id' => 100]], 200),
            '*/api2/invoices' => Http::response(['Invoice' => ['id' => 200]], 200),
            '*/api2/client_payments' => Http::response(['ClientPayment' => ['id' => 300]], 200),
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        // Verify: 3 API calls (client + invoice + payment)
        Http::assertSentCount(3);

        // Verify: Invoice was sent with correct data
        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/invoices')) return false;

            $data = $request->data();

            // Unit price should be the NET subtotal (450), NOT total
            $this->assertEquals(450, $data['InvoiceItem'][0]['unit_price']);

            // Quantity should be 1 (subtotal already includes price*qty+visit)
            $this->assertEquals(1, $data['InvoiceItem'][0]['quantity']);

            // Tax rate should be 15
            $this->assertEquals(15, $data['Invoice']['tax_rate']);

            // Discount should be 30 (coupons_total)
            $this->assertEquals(30, $data['Invoice']['discount']);
            $this->assertEquals(2, $data['Invoice']['discount_type']); // Fixed amount

            // Cost center should be set
            $this->assertEquals(1, $data['Invoice']['cost_center_id']);

            return true;
        });

        // Verify: Payment was routed to bank
        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/client_payments')) return false;

            $data = $request->data();

            // Payment amount = invoice amount
            $this->assertEquals(517.50, $data['ClientPayment']['amount']);

            // Treasury = bank account
            $this->assertEquals(2, $data['ClientPayment']['treasury_id']);

            return true;
        });

        // Verify: Invoice got daftra_id saved
        $invoice->refresh();
        $this->assertEquals(200, $invoice->daftra_id);
    }

    // ─────────────────────────────────────────────────────────
    // 4. ORDER WITHOUT COUPON — طلب بدون خصم
    // ─────────────────────────────────────────────────────────

    public function test_order_invoice_without_coupon_sends_no_discount(): void
    {
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'service_provider_id' => $this->serviceProvider->id,
            'service_id' => $this->service->id,
            'category_id' => $this->category->id,
            'quantity' => 1,
            'visit_cost' => 0,
            'subtotal' => 200,
            'tax_rate' => 15,
            'tax' => 30,
            'coupons_total' => 0, // No coupon
            'wallet_balance' => 0,
            'total' => 230,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'target_id' => $order->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 230,
        ]);

        Http::fake([
            '*/api2/clients' => Http::response(['Client' => ['id' => 100]], 200),
            '*/api2/invoices' => Http::response(['Invoice' => ['id' => 201]], 200),
            '*/api2/client_payments' => Http::response(['ClientPayment' => ['id' => 301]], 200),
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/invoices')) return false;

            $data = $request->data();

            // No discount key should be present
            $this->assertArrayNotHasKey('discount', $data['Invoice']);

            return true;
        });
    }

    // ─────────────────────────────────────────────────────────
    // 5. SUBSCRIPTION — الضريبة المضاعفة!
    // ─────────────────────────────────────────────────────────

    public function test_subscription_invoice_correctly_reverses_tax(): void
    {
        // Plan price = 100, tax = 15, paid_amount = 115 (inclusive of tax)
        // If we send 115 as unitPrice with taxRate 15%, Daftra calculates:
        //   115 + 17.25 = 132.25 ← WRONG (double tax!)
        //
        // Correct: net = 115 / 1.15 = 100
        // Daftra calculates: 100 + 15 = 115 ← CORRECT

        $plan = Plan::factory()->create([
            'name' => 'الخطة الأساسية',
            'price' => 100,
            'duration_in_days' => 30,
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $this->serviceProvider->id,
            'plan_id' => $plan->id,
            'paid_amount' => 115, // price + tax (inclusive!)
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 115,
            'target_id' => null, // target_id is NOT set (as per CreateSubscriptionInvoice)
        ]);

        Http::fake([
            '*/api2/clients' => Http::response(['Client' => ['id' => 100]], 200),
            '*/api2/invoices' => Http::response(['Invoice' => ['id' => 202]], 200),
            '*/api2/client_payments' => Http::response(['ClientPayment' => ['id' => 302]], 200),
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/invoices')) return false;

            $data = $request->data();

            // Net price should be 100 (115 / 1.15), NOT 115
            $this->assertEquals(100, $data['InvoiceItem'][0]['unit_price']);

            // Tax rate should be 15
            $this->assertEquals(15, $data['Invoice']['tax_rate']);

            // Cost center
            $this->assertEquals(1, $data['Invoice']['cost_center_id']);

            return true;
        });

        // Payment should be the full amount (115)
        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/client_payments')) return false;

            $data = $request->data();
            $this->assertEquals(115, $data['ClientPayment']['amount']);

            return true;
        });

        $invoice->refresh();
        $this->assertEquals(202, $invoice->daftra_id);
    }

    // ─────────────────────────────────────────────────────────
    // 6. CLIENT SYNC — عميل مُزامن مسبقاً لا يُنشأ مجدداً
    // ─────────────────────────────────────────────────────────

    public function test_existing_daftra_client_is_not_recreated(): void
    {
        // Customer already has a daftra_id
        $this->customer->update(['daftra_id' => 555]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'service_provider_id' => $this->serviceProvider->id,
            'service_id' => $this->service->id,
            'category_id' => $this->category->id,
            'quantity' => 1,
            'subtotal' => 100,
            'tax_rate' => 15,
            'tax' => 15,
            'coupons_total' => 0,
            'wallet_balance' => 0,
            'total' => 115,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'target_id' => $order->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 115,
        ]);

        Http::fake([
            '*/api2/invoices' => Http::response(['Invoice' => ['id' => 203]], 200),
            '*/api2/client_payments' => Http::response(['ClientPayment' => ['id' => 303]], 200),
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        // Client endpoint should NOT have been called (only invoice + payment)
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/clients');
        });
    }

    // ─────────────────────────────────────────────────────────
    // 7. MISSING ORDER — طلب محذوف
    // ─────────────────────────────────────────────────────────

    public function test_gracefully_handles_missing_order(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn($msg) => str_contains($msg, 'not found'));

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'target_id' => 999999, // Non-existent order
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 100,
        ]);

        Http::fake();

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        // No API calls
        Http::assertNothingSent();

        // Invoice should NOT have daftra_id
        $invoice->refresh();
        $this->assertNull($invoice->daftra_id);
    }

    // ─────────────────────────────────────────────────────────
    // 8. DTO TESTS — التحقق من البنية
    // ─────────────────────────────────────────────────────────

    public function test_invoice_dto_structure_with_discount(): void
    {
        $dto = new InvoiceDTO(
            clientId: 1,
            notes: 'Test',
            discount: 50,
            discountType: 2,
            taxRate: 15,
            costCenterId: 1,
        );

        $dto->addItem('خدمة تكييف', 300.00, 1);

        $array = $dto->toArray();

        // Verify structure
        $this->assertArrayHasKey('Invoice', $array);
        $this->assertArrayHasKey('InvoiceItem', $array);

        // Invoice fields
        $this->assertEquals(1, $array['Invoice']['client_id']);
        $this->assertEquals('SAR', $array['Invoice']['currency_code']);
        $this->assertEquals(15, $array['Invoice']['tax_rate']);
        $this->assertEquals(50, $array['Invoice']['discount']);
        $this->assertEquals(2, $array['Invoice']['discount_type']);
        $this->assertEquals(1, $array['Invoice']['cost_center_id']);

        // Should NOT have staff_id (old bug)
        $this->assertArrayNotHasKey('staff_id', $array['Invoice']);

        // Items
        $this->assertCount(1, $array['InvoiceItem']);
        $this->assertEquals(300.00, $array['InvoiceItem'][0]['unit_price']);
    }

    public function test_invoice_dto_structure_without_discount(): void
    {
        $dto = new InvoiceDTO(clientId: 1);
        $dto->addItem('خدمة', 200.00);

        $array = $dto->toArray();

        // No discount keys
        $this->assertArrayNotHasKey('discount', $array['Invoice']);
        $this->assertArrayNotHasKey('discount_type', $array['Invoice']);
    }

    public function test_payment_dto_structure(): void
    {
        $dto = new PaymentDTO(
            invoiceId: 100,
            amount: 517.50,
            clientId: 1,
            treasuryId: 2,
        );

        $array = $dto->toArray();

        $this->assertArrayHasKey('ClientPayment', $array);
        $this->assertEquals(100, $array['ClientPayment']['invoice_id']);
        $this->assertEquals(517.50, $array['ClientPayment']['amount']);
        $this->assertEquals(2, $array['ClientPayment']['treasury_id']);
        $this->assertEquals(1, $array['ClientPayment']['type']); // Receipt
    }

    // ─────────────────────────────────────────────────────────
    // 9. DISPATCH FROM LISTENER — التأكد من إطلاق الـ Job
    // ─────────────────────────────────────────────────────────

    public function test_order_completed_dispatches_daftra_sync_job(): void
    {
        Queue::fake();

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'service_provider_id' => $this->serviceProvider->id,
            'service_id' => $this->service->id,
            'category_id' => $this->category->id,
            'subtotal' => 200,
            'tax_rate' => 15,
            'tax' => 30,
            'coupons_total' => 0,
            'wallet_balance' => 0,
            'total' => 230,
            'status' => Order::COMPLETED_STATUS,
        ]);

        // Fire the event manually
        event(new \App\Events\OrderCompleted($order));

        Queue::assertPushed(SyncInvoiceToDaftra::class);
    }

    // ─────────────────────────────────────────────────────────
    // 10. REVENUE ACCOUNT — إيراد مبيعات تشييك #28
    // ─────────────────────────────────────────────────────────

    public function test_invoice_items_include_revenue_account_id(): void
    {
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'service_provider_id' => $this->serviceProvider->id,
            'service_id' => $this->service->id,
            'category_id' => $this->category->id,
            'subtotal' => 200,
            'tax_rate' => 15,
            'tax' => 30,
            'coupons_total' => 0,
            'wallet_balance' => 0,
            'total' => 230,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $this->serviceProvider->id,
            'target_id' => $order->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 230,
        ]);

        Http::fake([
            '*/api2/clients' => Http::response(['Client' => ['id' => 100]], 200),
            '*/api2/invoices' => Http::response(['Invoice' => ['id' => 204]], 200),
            '*/api2/client_payments' => Http::response(['ClientPayment' => ['id' => 304]], 200),
        ]);

        $job = new SyncInvoiceToDaftra($invoice);
        $job->handle(app(Daftra::class));

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/invoices')) return false;

            $data = $request->data();

            // Each item should have income_account_id = 28 (إيراد مبيعات تشييك)
            $this->assertEquals(28, $data['InvoiceItem'][0]['income_account_id']);

            return true;
        });
    }

    // ─────────────────────────────────────────────────────────
    // 11. CREDIT NOTE DTO — بنية الإشعار الدائن
    // ─────────────────────────────────────────────────────────

    public function test_credit_note_dto_structure(): void
    {
        $dto = new CreditNoteDTO(
            clientId: 1,
            notes: 'إلغاء طلب #123',
            taxRate: 15,
            costCenterId: 1,
            returnAccountId: 368,
        );

        $dto->addItem('خدمة تكييف', 200.00);

        $array = $dto->toArray();

        // Verify wrapper key
        $this->assertArrayHasKey('CreditNote', $array);
        $this->assertArrayHasKey('InvoiceItem', $array);

        // Credit note fields
        $this->assertEquals(1, $array['CreditNote']['client_id']);
        $this->assertEquals(1, $array['CreditNote']['cost_center_id']);
        $this->assertEquals(15, $array['CreditNote']['tax_rate']);

        // Item with return account
        $this->assertEquals(200.00, $array['InvoiceItem'][0]['unit_price']);
        $this->assertEquals(368, $array['InvoiceItem'][0]['income_account_id']);
    }

    // ─────────────────────────────────────────────────────────
    // 12. CREDIT NOTE JOB — مردود المبيعات عند الإلغاء
    // ─────────────────────────────────────────────────────────

    public function test_credit_note_syncs_for_existing_daftra_client(): void
    {
        // Customer must already exist in Daftra
        $this->customer->update(['daftra_id' => 555]);

        Http::fake([
            '*/api2/credit_notes' => Http::response(['CreditNote' => ['id' => 400]], 200),
        ]);

        $job = new SyncCreditNoteToDaftra(
            customerId: $this->customer->id,
            serviceName: 'خدمة تكييف',
            subtotal: 200.00,
            taxRate: 15,
            orderId: 999,
        );

        $job->handle(app(Daftra::class));

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), '/credit_notes')) return false;

            $data = $request->data();

            // Client should be the Daftra ID
            $this->assertEquals(555, $data['CreditNote']['client_id']);

            // Item should route to return account #368
            $this->assertEquals(200.00, $data['InvoiceItem'][0]['unit_price']);
            $this->assertEquals(368, $data['InvoiceItem'][0]['income_account_id']);

            // Cost center
            $this->assertEquals(1, $data['CreditNote']['cost_center_id']);

            return true;
        });
    }

    public function test_credit_note_skips_if_customer_not_in_daftra(): void
    {
        // Customer does NOT have daftra_id
        Http::fake();

        $job = new SyncCreditNoteToDaftra(
            customerId: $this->customer->id,
            serviceName: 'خدمة',
            subtotal: 100.00,
            taxRate: 15,
            orderId: 888,
        );

        $job->handle(app(Daftra::class));

        // No API calls (customer not in Daftra = skip)
        Http::assertNothingSent();
    }
}

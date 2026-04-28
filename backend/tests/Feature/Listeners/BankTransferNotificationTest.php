<?php

namespace Tests\Feature\Listeners;

use App\Events\NewBankTransfer;
use App\Listeners\CreateBankTransferInvoice;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\User;
use App\Utils\Services\Daftra;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BankTransferNotificationTest extends TestCase
{
    public function test_bank_transfer_creates_pending_accounting_notification(): void
    {
        config(['services.daftra.api_key' => null]);

        $serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'name' => 'SP Test',
        ]);

        $listener = new CreateBankTransferInvoice;
        $listener->handle(new NewBankTransfer($serviceProvider, 200.0, 99), app(Daftra::class));

        $invoice = Invoice::query()
            ->where('event_uid', 'payout:'.$serviceProvider->id.':request:99:type:'.Invoice::BANK_TRANSFER_TYPE)
            ->first();

        $this->assertNotNull($invoice);
        $this->assertFalse((bool) $invoice->recorded_in_daftra);

        $notification = Notification::query()
            ->where('type', 'bank-transfer-daftra-pending')
            ->latest('id')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('فشل مزامنة تحويل بنكي إلى دفتره', $notification->getTranslation('title', 'ar'));
        $this->assertEquals('Bank transfer Daftra sync failed', $notification->getTranslation('title', 'en'));
        $payload = json_decode((string) $notification->data, true);

        $this->assertEquals($invoice->id, $payload['invoice_id'] ?? null);
        $this->assertEquals($serviceProvider->id, $payload['service_provider_id'] ?? null);
        $this->assertFalse((bool) ($payload['recorded_in_daftra'] ?? true));
    }

    public function test_bank_transfer_auto_sync_marks_invoice_recorded_when_daftra_accepts_expense(): void
    {
        config([
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.bank_account_id' => 1,
            'services.daftra.sp_payout_account_id' => 51,
        ]);

        Http::fake([
            '*/api2/expenses' => Http::response(['Expense' => ['id' => 501]], 200),
        ]);

        $serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'name' => 'SP Auto',
        ]);

        $listener = new CreateBankTransferInvoice;
        $listener->handle(new NewBankTransfer($serviceProvider, 120.0, 100), app(Daftra::class));

        $invoice = Invoice::query()
            ->where('event_uid', 'payout:'.$serviceProvider->id.':request:100:type:'.Invoice::BANK_TRANSFER_TYPE)
            ->first();

        $this->assertNotNull($invoice);
        $this->assertTrue((bool) $invoice->recorded_in_daftra);
        $this->assertEquals(501, $invoice->daftra_payment_id);

        $pendingNotification = Notification::query()
            ->where('type', 'bank-transfer-daftra-pending')
            ->latest('id')
            ->first();

        $this->assertNull($pendingNotification);
    }
}

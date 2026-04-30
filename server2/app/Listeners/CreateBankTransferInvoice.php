<?php

namespace App\Listeners;

use App\Events\NewBankTransfer;
use App\Models\Invoice;
use App\Models\Notification;
use App\Support\ServiceProviderInvoiceMailer;
use App\Utils\Services\Daftra;
use App\Utils\Services\Daftra\DTOs\ExpenseDTO;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateBankTransferInvoice implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewBankTransfer $event, Daftra $daftra): void
    {
        $payoutRef = $event->payoutRequestId ?? 'unknown';
        $eventUid = "payout:{$event->serviceProvider->id}:request:{$payoutRef}:type:".Invoice::BANK_TRANSFER_TYPE;

        $invoice = Invoice::firstOrCreate(
            ['event_uid' => $eventUid],
            [
                'service_provider_id' => $event->serviceProvider->id,
                'type' => Invoice::BANK_TRANSFER_TYPE,
                'action' => Invoice::DEBIT_ACTION,
                'amount' => $event->amount,
            ],
        );

        if ($invoice->recorded_in_daftra) {
            return;
        }

        if ($invoice->wasRecentlyCreated) {
            ServiceProviderInvoiceMailer::send($invoice);
        }

        $expenseId = $this->syncPayoutToDaftra($invoice, $event, $daftra);
        if ($expenseId) {
            $invoice->update([
                'recorded_in_daftra' => true,
                'recorded_in_daftra_at' => now(),
                'daftra_payment_id' => $expenseId,
            ]);

            return;
        }

        $this->createPendingNotification($invoice, $event);
    }

    private function syncPayoutToDaftra(Invoice $invoice, NewBankTransfer $event, Daftra $daftra): ?int
    {
        $treasuryId = (int) ($daftra->getConfig('bank_account_id') ?: 1);
        $payoutAccountId = (int) ($daftra->getConfig('sp_payout_account_id') ?: 51);
        if ($treasuryId <= 0 || $payoutAccountId <= 0) {
            return null;
        }

        $dto = new ExpenseDTO(
            amount: (float) $invoice->amount,
            treasuryId: $treasuryId,
            journalAccountId: $payoutAccountId,
            notes: "تحويل بنكي لمقدم الخدمة #{$event->serviceProvider->id} | invoice #{$invoice->id}",
        );

        return $daftra->createExpense($dto);
    }

    private function createPendingNotification(Invoice $invoice, NewBankTransfer $event): void
    {
        $exists = Notification::query()
            ->where('type', 'bank-transfer-daftra-pending')
            ->get()
            ->contains(function (Notification $notification) use ($invoice): bool {
                $payload = json_decode((string) $notification->data, true);

                return (int) ($payload['invoice_id'] ?? 0) === (int) $invoice->id;
            });

        if ($exists) {
            return;
        }

        Notification::create([
            'type' => 'bank-transfer-daftra-pending',
            'title' => [
                'ar' => 'فشل مزامنة تحويل بنكي إلى دفتره',
                'en' => 'Bank transfer Daftra sync failed',
            ],
            'description' => [
                'ar' => "تعذر مزامنة تحويل بنكي لمقدم الخدمة {$event->serviceProvider->name} بقيمة {$event->amount}. رقم الفاتورة #{$invoice->id}.",
                'en' => "Failed to sync bank transfer payout for {$event->serviceProvider->name} amount {$event->amount}. Invoice #{$invoice->id}.",
            ],
            'data' => json_encode([
                'invoice_id' => $invoice->id,
                'service_provider_id' => $event->serviceProvider->id,
                'service_provider_name' => $event->serviceProvider->name,
                'amount' => (float) $event->amount,
                'payout_request_id' => $event->payoutRequestId,
                'recorded_in_daftra' => false,
                'auto_sync_failed' => true,
            ]),
        ]);
    }
}

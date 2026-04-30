<?php

namespace App\Listeners;

use App\Events\PlanPaid;
use App\Jobs\SyncInvoiceToDaftra;
use App\Models\Invoice;
use App\Support\SubscriptionPlanPaidMailer;
use App\Utils\Traits\HasTax;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateSubscriptionInvoice implements ShouldQueue
{
    use HasTax;

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
    public function handle(PlanPaid $event): void
    {
        $serviceProviderId = (int) $event->data['service_provider_id'];
        $eventRef = $event->data['transaction_id']
            ?? $event->data['paymob_transaction_id']
            ?? $event->data['id']
            ?? md5(json_encode($event->data));

        // المبلغ الإجمالي المدفوع (شامل الضريبة) — بدونه لا فاتورة صالحة ولا Daftra
        $paidGross = (float) ($event->data['paid_amount'] ?? 0);
        if ($paidGross <= 0) {
            Log::warning('CreateSubscriptionInvoice: skipped — paid_amount missing or zero (no invoice, no Daftra)', [
                'service_provider_id' => $serviceProviderId,
                'event_ref' => $eventRef,
            ]);

            return;
        }

        $tax = $this->getTaxes($paidGross);

        $invoice = DB::transaction(function () use ($serviceProviderId, $eventRef, $tax, $paidGross): Invoice {
            $mainInvoice = Invoice::firstOrCreate(
                ['event_uid' => "plan_paid:{$eventRef}:type:".Invoice::RENEW_SUBSCRIPTION_TYPE],
                [
                    'service_provider_id' => $serviceProviderId,
                    'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
                    'action' => Invoice::DEBIT_ACTION,
                    'amount' => $paidGross,
                ],
            );

            // Create tax invoice once per payment event.
            if ($tax) {
                Invoice::firstOrCreate(
                    ['event_uid' => "plan_paid:{$eventRef}:type:".Invoice::RENEW_SUBSCRIPTION_TAX_TYPE],
                    [
                        'service_provider_id' => $serviceProviderId,
                        'type' => Invoice::RENEW_SUBSCRIPTION_TAX_TYPE,
                        'action' => Invoice::DEBIT_ACTION,
                        'amount' => $tax,
                    ],
                );
            }

            return $mainInvoice;
        });

        if ($invoice->wasRecentlyCreated) {
            SubscriptionPlanPaidMailer::send($invoice);
        }

        // Sync main invoice with Daftra ERP in Background
        // bankAmount = paid_amount - wallet_balance is what actually reached
        // the payment gateway (after wallet deduction). If the renewal was paid
        // entirely from the service provider's wallet, bankAmount == 0 and the
        // job will skip the bank receipt entirely.
        $walletAmount = (float) ($event->data['wallet_balance'] ?? 0);
        $bankAmount = max(0, $paidGross - $walletAmount);

        if ($invoice->wasRecentlyCreated) {
            SyncInvoiceToDaftra::dispatch($invoice, bankAmount: $bankAmount);
        }
    }
}

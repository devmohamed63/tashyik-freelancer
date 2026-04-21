<?php

namespace App\Listeners;

use App\Events\PlanPaid;
use App\Models\Invoice;
use App\Utils\Traits\HasTax;
use App\Jobs\SyncInvoiceToDaftra;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateSubscriptionInvoice
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
        $invoice = new Invoice();
        $invoice->service_provider_id = $event->data['service_provider_id'];
        $invoice->type = Invoice::RENEW_SUBSCRIPTION_TYPE;
        $invoice->action = Invoice::DEBIT_ACTION;
        $invoice->amount = $event->data['paid_amount'];
        $invoice->save();

        $tax = $this->getTaxes($event->data['paid_amount']);

        // Create tax invoice
        if ($tax) {
            $taxInvoice = new Invoice();
            $taxInvoice->service_provider_id = $event->data['service_provider_id'];
            $taxInvoice->type = Invoice::RENEW_SUBSCRIPTION_TAX_TYPE;
            $taxInvoice->action = Invoice::DEBIT_ACTION;
            $taxInvoice->amount = $tax;
            $taxInvoice->save();
        }

        // Sync main invoice with Daftra ERP in Background
        // bankAmount = paid_amount - wallet_balance is what actually reached
        // the payment gateway (after wallet deduction). If the renewal was paid
        // entirely from the service provider's wallet, bankAmount == 0 and the
        // job will skip the bank receipt entirely.
        $paidAmount   = (float) ($event->data['paid_amount'] ?? 0);
        $walletAmount = (float) ($event->data['wallet_balance'] ?? 0);
        $bankAmount   = max(0, $paidAmount - $walletAmount);

        SyncInvoiceToDaftra::dispatch($invoice, bankAmount: $bankAmount);
    }
}

<?php

namespace App\Listeners;

use App\Events\PlanPaid;
use App\Models\Invoice;
use App\Utils\Traits\HasTax;
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
    }
}

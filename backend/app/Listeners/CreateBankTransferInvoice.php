<?php

namespace App\Listeners;

use App\Events\NewBankTransfer;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateBankTransferInvoice
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
    public function handle(NewBankTransfer $event): void
    {
        $invoice = new Invoice();
        $invoice->service_provider_id = $event->serviceProvider->id;
        $invoice->type = Invoice::BANK_TRANSFER_TYPE;
        $invoice->action = Invoice::DEBIT_ACTION;
        $invoice->amount = $event->amount;
        $invoice->save();
    }
}

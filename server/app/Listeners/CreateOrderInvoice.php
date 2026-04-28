<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Invoice;
use App\Jobs\SyncInvoiceToDaftra;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderInvoice
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
    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $serviceProvider = $order->serviceProvider;

        // Create order invoice
        if ($order->subtotal) {
            $invoice = new Invoice();
            $invoice->service_provider_id = $serviceProvider->id;
            $invoice->target_id = $order->id;
            $invoice->type = Invoice::COMPLETED_ORDER_TYPE;
            $invoice->action = Invoice::CREDIT_ACTION;
            $invoice->amount = $order->subtotal + $order->tax;
            $invoice->save();
        }

        if ($order->tax) {
            // Create order tax invoice
            $taxInvoice = new Invoice();
            $taxInvoice->service_provider_id = $serviceProvider->id;
            $taxInvoice->target_id = $order->id;
            $taxInvoice->type = Invoice::COMPLETED_ORDER_TAX_TYPE;
            $taxInvoice->action = Invoice::DEBIT_ACTION;
            $taxInvoice->amount = $order->tax;
            $taxInvoice->save();
        }

        // Credit balance to institution if member, otherwise to individual
        $creditTarget = $serviceProvider->institution_id
            ? $serviceProvider->institution
            : $serviceProvider;

        $creditTarget->increment('balance', $order->subtotal);

        // Sync with Daftra ERP in Background
        // bankAmount = order.total is what actually reached the payment gateway
        // (i.e. after coupons and wallet deductions). This is what we must
        // record as a bank receipt in Daftra — not invoice.amount which is the
        // gross (subtotal + tax).
        if (isset($invoice)) {
            SyncInvoiceToDaftra::dispatch(
                $invoice,
                bankAmount: (float) ($order->total ?? 0),
            );
        }
    }
}

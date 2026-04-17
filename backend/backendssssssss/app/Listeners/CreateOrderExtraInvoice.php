<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderExtraInvoice
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

        foreach ($order->orderExtras()->paid()->get() as $orderExtra) {
            // Create order extra invoice
            if ($orderExtra->price) {
                $invoice = new Invoice();
                $invoice->service_provider_id = $serviceProvider->id;
                $invoice->target_id = $orderExtra->order_id;
                $invoice->type = Invoice::ADDITIONAL_SERVICES_TYPE;
                $invoice->action = Invoice::CREDIT_ACTION;
                $invoice->amount = $orderExtra->price + $orderExtra->tax + $orderExtra->materials;
                $invoice->save();
            }

            // Create order extra tax invoice
            if ($orderExtra->tax) {
                $taxInvoice = new Invoice();
                $taxInvoice->service_provider_id = $serviceProvider->id;
                $taxInvoice->target_id = $orderExtra->order_id;
                $taxInvoice->type = Invoice::ADDITIONAL_SERVICES_TAX_TYPE;
                $taxInvoice->action = Invoice::DEBIT_ACTION;
                $taxInvoice->amount = $orderExtra->tax;
                $taxInvoice->save();
            }

            // Credit balance to institution if member, otherwise to individual
            $creditTarget = $serviceProvider->institution_id
                ? $serviceProvider->institution
                : $serviceProvider;

            $creditTarget->increment('balance', $orderExtra->price + $orderExtra->materials);
        }
    }
}

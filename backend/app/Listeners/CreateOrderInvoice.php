<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\SyncInvoiceToDaftra;
use App\Models\Invoice;
use App\Support\ServiceProviderInvoiceMailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CreateOrderInvoice implements ShouldQueue
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
        $mainInvoice = null;
        $mainInvoiceCreated = false;

        DB::transaction(function () use (
            $order,
            $serviceProvider,
            &$mainInvoice,
            &$mainInvoiceCreated,
        ): void {
            // Create order invoice once per order
            if ($order->subtotal) {
                $mainInvoice = Invoice::firstOrCreate(
                    ['event_uid' => "order:{$order->id}:type:".Invoice::COMPLETED_ORDER_TYPE],
                    [
                        'service_provider_id' => $serviceProvider->id,
                        'target_id' => $order->id,
                        'type' => Invoice::COMPLETED_ORDER_TYPE,
                        'action' => Invoice::CREDIT_ACTION,
                        'amount' => $order->subtotal + $order->tax,
                    ],
                );
                $mainInvoiceCreated = $mainInvoice->wasRecentlyCreated;
            }

            if ($order->tax) {
                // Create order tax invoice once per order
                Invoice::firstOrCreate(
                    ['event_uid' => "order:{$order->id}:type:".Invoice::COMPLETED_ORDER_TAX_TYPE],
                    [
                        'service_provider_id' => $serviceProvider->id,
                        'target_id' => $order->id,
                        'type' => Invoice::COMPLETED_ORDER_TAX_TYPE,
                        'action' => Invoice::DEBIT_ACTION,
                        'amount' => $order->tax,
                    ],
                );
            }

            if ($mainInvoiceCreated) {
                // Credit balance once, in the same idempotent boundary.
                $creditTarget = $serviceProvider->institution_id
                    ? $serviceProvider->institution
                    : $serviceProvider;

                $creditTarget->increment('balance', $order->subtotal);
            }
        });

        if ($mainInvoiceCreated && $mainInvoice) {
            ServiceProviderInvoiceMailer::send($mainInvoice);
        }

        // Sync with Daftra ERP in Background
        // bankAmount = order.total is what actually reached the payment gateway
        // (i.e. after coupons and wallet deductions). This is what we must
        // record as a bank receipt in Daftra — not invoice.amount which is the
        // gross (subtotal + tax).
        if ($mainInvoiceCreated && $mainInvoice) {
            SyncInvoiceToDaftra::dispatch(
                $mainInvoice,
                bankAmount: (float) ($order->total ?? 0),
            );
        }
    }
}

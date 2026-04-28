<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\SyncInvoiceToDaftra;
use App\Models\Invoice;
use App\Support\ServiceProviderInvoiceMailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class CreateOrderExtraInvoice implements ShouldQueue
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
            $baseAmount = (float) ($orderExtra->price + $orderExtra->materials);
            $mainInvoice = null;
            $mainInvoiceCreated = false;

            DB::transaction(function () use (
                $orderExtra,
                $serviceProvider,
                $baseAmount,
                &$mainInvoice,
                &$mainInvoiceCreated,
            ): void {
                // Create order extra invoice once per paid order extra.
                if ($baseAmount > 0) {
                    $mainInvoice = Invoice::firstOrCreate(
                        ['event_uid' => "order_extra:{$orderExtra->id}:type:".Invoice::ADDITIONAL_SERVICES_TYPE],
                        [
                            'service_provider_id' => $serviceProvider->id,
                            'target_id' => $orderExtra->order_id,
                            'type' => Invoice::ADDITIONAL_SERVICES_TYPE,
                            'action' => Invoice::CREDIT_ACTION,
                            'amount' => $orderExtra->price + $orderExtra->tax + $orderExtra->materials,
                        ],
                    );
                    $mainInvoiceCreated = $mainInvoice->wasRecentlyCreated;
                }

                // Create order extra tax invoice once per paid order extra.
                if ($orderExtra->tax) {
                    Invoice::firstOrCreate(
                        ['event_uid' => "order_extra:{$orderExtra->id}:type:".Invoice::ADDITIONAL_SERVICES_TAX_TYPE],
                        [
                            'service_provider_id' => $serviceProvider->id,
                            'target_id' => $orderExtra->order_id,
                            'type' => Invoice::ADDITIONAL_SERVICES_TAX_TYPE,
                            'action' => Invoice::DEBIT_ACTION,
                            'amount' => $orderExtra->tax,
                        ],
                    );
                }

                if ($mainInvoiceCreated) {
                    // Credit balance once, in the same idempotent boundary.
                    $creditTarget = $serviceProvider->institution_id
                        ? $serviceProvider->institution
                        : $serviceProvider;

                    $creditTarget->increment('balance', $baseAmount);
                }
            });

            if ($mainInvoiceCreated && $mainInvoice) {
                ServiceProviderInvoiceMailer::send($mainInvoice);

                SyncInvoiceToDaftra::dispatch(
                    $mainInvoice,
                    bankAmount: (float) ($orderExtra->total ?? 0),
                );
            }
        }
    }
}

<?php

namespace App\Observers;

use App\Jobs\SyncServiceProviderToPineconeJob;
use App\Models\Order;

class ServiceProviderOrderObserver
{
    public function created(Order $order): void
    {
        $this->syncCurrentProvider($order);
    }

    public function updated(Order $order): void
    {
        $this->syncCurrentProvider($order);

        if ($order->wasChanged('service_provider_id')) {
            $oldProviderId = (int) $order->getOriginal('service_provider_id');
            if ($oldProviderId > 0) {
                SyncServiceProviderToPineconeJob::dispatch($oldProviderId);
            }
        }
    }

    public function deleted(Order $order): void
    {
        $this->syncCurrentProvider($order);
    }

    public function restored(Order $order): void
    {
        $this->syncCurrentProvider($order);
    }

    private function syncCurrentProvider(Order $order): void
    {
        $serviceProviderId = (int) $order->service_provider_id;
        if ($serviceProviderId <= 0) {
            return;
        }

        SyncServiceProviderToPineconeJob::dispatch($serviceProviderId);
    }
}

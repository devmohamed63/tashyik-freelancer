<?php

namespace App\Observers;

use App\Jobs\DeleteServiceFromPineconeJob;
use App\Jobs\SyncServiceToPineconeJob;
use App\Models\Service;

class ServiceObserver
{
    public function updated(Service $service): void
    {
        SyncServiceToPineconeJob::dispatch($service->id)->afterResponse();
    }

    public function deleted(Service $service): void
    {
        DeleteServiceFromPineconeJob::dispatch($service->id)->afterResponse();
    }

    public function restored(Service $service): void
    {
        SyncServiceToPineconeJob::dispatch($service->id)->afterResponse();
    }
}

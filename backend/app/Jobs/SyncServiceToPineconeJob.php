<?php

namespace App\Jobs;

use App\Services\Pinecone\PineconeServiceCatalogSyncer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncServiceToPineconeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 90];

    public function __construct(public int $serviceId)
    {
    }

    public function handle(PineconeServiceCatalogSyncer $syncer): void
    {
        $syncer->syncById($this->serviceId);
    }
}

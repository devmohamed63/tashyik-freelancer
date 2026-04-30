<?php

namespace App\Jobs;

use App\Services\Pinecone\PineconeServiceProviderSyncer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteServiceProviderFromPineconeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 90];

    public function __construct(public int $serviceProviderId)
    {
    }

    public function handle(PineconeServiceProviderSyncer $syncer): void
    {
        $syncer->deleteById($this->serviceProviderId);
    }
}

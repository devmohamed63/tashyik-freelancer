<?php

namespace App\Services\Pinecone;

use App\Models\Service;

class PineconeServiceCatalogSyncer
{
    public function __construct(
        private readonly PineconeService $pinecone,
        private readonly ServiceCatalogRecordBuilder $recordBuilder,
    ) {
    }

    public function syncById(int $serviceId): void
    {
        $service = Service::query()->find($serviceId);

        if (! $service) {
            $this->deleteById($serviceId);

            return;
        }

        $record = $this->recordBuilder->build($service);
        $this->pinecone->upsertRecord($record);
    }

    public function deleteById(int $serviceId): void
    {
        $this->pinecone->deleteRecord('svc_'.$serviceId);
    }
}

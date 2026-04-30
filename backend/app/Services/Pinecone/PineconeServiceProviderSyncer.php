<?php

namespace App\Services\Pinecone;

use App\Models\User;

class PineconeServiceProviderSyncer
{
    public function __construct(
        private readonly PineconeService $pinecone,
        private readonly ServiceProviderRecordBuilder $recordBuilder,
    ) {
    }

    public function syncById(int $serviceProviderId): void
    {
        $serviceProvider = User::query()
            ->with([
                'city',
                'categories',
                'reviews.user',
                'serviceProviderOrders.customer.city',
                'serviceProviderOrders.category',
                'serviceProviderOrders.service',
            ])
            ->find($serviceProviderId);

        if (! $serviceProvider || $serviceProvider->type !== User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            $this->deleteById($serviceProviderId);

            return;
        }

        $record = $this->recordBuilder->build($serviceProvider);
        $this->pinecone->upsertRecord($record);
    }

    public function deleteById(int $serviceProviderId): void
    {
        $this->pinecone->deleteRecord((string) $serviceProviderId);
    }
}

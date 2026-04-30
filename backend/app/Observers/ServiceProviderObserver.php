<?php

namespace App\Observers;

use App\Jobs\DeleteServiceProviderFromPineconeJob;
use App\Jobs\SyncServiceProviderToPineconeJob;
use App\Models\User;

class ServiceProviderObserver
{
    public function created(User $user): void
    {
        if (! $this->isServiceProvider($user)) {
            return;
        }

        SyncServiceProviderToPineconeJob::dispatch($user->id);
    }

    public function updated(User $user): void
    {
        if ($this->isServiceProvider($user)) {
            SyncServiceProviderToPineconeJob::dispatch($user->id);

            return;
        }

        // If account type changed away from service provider, remove stale index record.
        if ($user->wasChanged('type')) {
            DeleteServiceProviderFromPineconeJob::dispatch($user->id);
        }
    }

    public function deleted(User $user): void
    {
        if (! $this->isServiceProvider($user)) {
            return;
        }

        DeleteServiceProviderFromPineconeJob::dispatch($user->id);
    }

    public function restored(User $user): void
    {
        if (! $this->isServiceProvider($user)) {
            return;
        }

        SyncServiceProviderToPineconeJob::dispatch($user->id);
    }

    private function isServiceProvider(User $user): bool
    {
        return $user->type === User::SERVICE_PROVIDER_ACCOUNT_TYPE;
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\SyncServiceProviderToPineconeJob;
use App\Models\User;
use Illuminate\Console\Command;

class SyncServiceProvidersToPinecone extends Command
{
    protected $signature = 'pinecone:sync-service-providers {--sync : Run immediately instead of queueing jobs}';

    protected $description = 'Sync all service providers to Pinecone';

    public function handle(): int
    {
        $syncInline = (bool) $this->option('sync');
        $count = 0;

        User::query()
            ->where('type', User::SERVICE_PROVIDER_ACCOUNT_TYPE)
            ->select('id')
            ->chunkById(200, function ($users) use (&$count, $syncInline): void {
                foreach ($users as $user) {
                    if ($syncInline) {
                        SyncServiceProviderToPineconeJob::dispatchSync((int) $user->id);
                    } else {
                        SyncServiceProviderToPineconeJob::dispatch((int) $user->id);
                    }

                    $count++;
                }
            });

        $mode = $syncInline ? 'synchronously' : 'via queue';
        $this->info("Dispatched {$count} service provider sync jobs {$mode}.");

        return self::SUCCESS;
    }
}

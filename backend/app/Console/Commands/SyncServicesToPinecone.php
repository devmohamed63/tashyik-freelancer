<?php

namespace App\Console\Commands;

use App\Jobs\SyncServiceToPineconeJob;
use App\Models\Service;
use Illuminate\Console\Command;

class SyncServicesToPinecone extends Command
{
    protected $signature = 'pinecone:sync-services {--sync : Run immediately instead of queueing jobs}';

    protected $description = 'Sync all catalog services to Pinecone';

    public function handle(): int
    {
        $syncInline = (bool) $this->option('sync');
        $count = 0;

        Service::query()
            ->select('id')
            ->chunkById(200, function ($services) use (&$count, $syncInline): void {
                foreach ($services as $service) {
                    if ($syncInline) {
                        SyncServiceToPineconeJob::dispatchSync((int) $service->id);
                    } else {
                        SyncServiceToPineconeJob::dispatch((int) $service->id);
                    }

                    $count++;
                }
            });

        $mode = $syncInline ? 'synchronously' : 'via queue';
        $this->info("Dispatched {$count} service catalog sync jobs {$mode}.");

        return self::SUCCESS;
    }
}

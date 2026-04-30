<?php

namespace App\Console\Commands;

use App\Utils\Services\Firebase\Firestore;
use Illuminate\Console\Command;

class ResetFirestoreAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-firestore-analytics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all of the documents of firestore analytics database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $firestore = new Firestore();
        $categoryDocuments = $firestore->getDocuments('category_analytics');
        $CityDocuments = $firestore->getDocuments('city_analytics');

        $documents = array_merge($categoryDocuments, $CityDocuments);

        $writes = [];

        foreach ($documents as $document) {
            array_push($writes, [
                'update' => [
                    'name' => $document['name'],
                    'fields' => [
                        'count' => ['integerValue' => 0]
                    ]
                ],
            ]);
        }

        $firestore->runWrites($writes);
    }
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\City;
use App\Models\User;

\DB::enableQueryLog();

$query = City::query()
    ->select(['id', 'name'])
    ->withCount('serviceProviders')
    ->having('service_providers_count', '>=', 20);

try {
    $cities = $query->paginate(10);
    echo "Paginate works. Cities found on page 1: " . $cities->count() . "\n";
    dd(\DB::getQueryLog());
} catch (\Exception $e) {
    echo "PAGINATE ERROR: " . $e->getMessage() . "\n";
}

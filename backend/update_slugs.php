<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use Illuminate\Support\Str;

$services = Service::all();
foreach ($services as $service) {
    if (empty($service->slug)) {
        $service->slug = Service::generateUniqueSlug($service->getTranslation('name', 'ar', false) ?: $service->getTranslation('name', 'en', false) ?: 'srv-' . $service->id);
        $service->save();
        echo "Updated service {$service->id} with slug: {$service->slug}\n";
    }
}

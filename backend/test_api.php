<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/dashboard/technician-map/city-insights', 'GET')
);

echo "STATUS: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() !== 200) {
    echo $response->getContent();
} else {
    echo "SUCCESS_API\n";
}

$response2 = $kernel->handle(
    $request2 = Illuminate\Http\Request::create('/dashboard/technician-map/api', 'GET')
);

echo "STATUS API: " . $response2->getStatusCode() . "\n";
if ($response2->getStatusCode() !== 200) {
    echo $response2->getContent();
} else {
    echo "SUCCESS_MAIN_API\n";
}

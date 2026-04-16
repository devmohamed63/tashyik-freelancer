<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the application properly
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate admin user
$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

$request = \Illuminate\Http\Request::create('/technician-map', 'GET');
$request->headers->set('Host', 'dashboard.localhost');

try {
    $response = $kernel->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . PHP_EOL;
    if ($response->getStatusCode() >= 400) {
        $content = $response->getContent();
        // Try to extract error from Whoops/Ignition
        if (preg_match('/exception_message["\s:]+(.+?)["<]/s', $content, $m)) {
            echo 'MESSAGE: ' . trim(strip_tags($m[1])) . PHP_EOL;
        }
        if (preg_match('/<h2 class="exception-message">(.*?)<\/h2>/s', $content, $m)) {
            echo 'MESSAGE: ' . trim(strip_tags($m[1])) . PHP_EOL;
        }
        // Get Laravel log
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lastLines = array_slice($lines, -30);
            foreach ($lastLines as $line) {
                if (stripos($line, 'ERROR') !== false || stripos($line, 'exception') !== false) {
                    echo trim($line) . PHP_EOL;
                }
            }
        }
    } else {
        echo 'OK - page rendered successfully (' . strlen($response->getContent()) . ' bytes)' . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'FILE: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}

$kernel->terminate($request, $response ?? null);

<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    \Illuminate\Support\Facades\Mail::raw('This is a test email to verify your SMTP settings.', function ($msg) {
        $msg->to('mohamed202203785@gmail.com')
            ->subject('SMTP Verification Test');
    });
    echo "SUCCESS: Email sent successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Set the configuration from the provided credentials dynamically
config([
    'mail.mailers.smtp.transport' => 'smtp',
    'mail.mailers.smtp.host' => 'smtp.gmail.com',
    'mail.mailers.smtp.port' => 587,
    'mail.mailers.smtp.encryption' => 'tls',
    'mail.mailers.smtp.username' => 'mohmahmoudd63@gmail.com',
    'mail.mailers.smtp.password' => 'cbzrsxstroikrjwo',
    'mail.from.address' => 'mohmahmoudd63@gmail.com',
    'mail.from.name' => config('app.name'),
]);

try {
    \Illuminate\Support\Facades\Mail::raw('هذه رسالة تجريبية لاختبار إعدادات الـ SMTP الخاصة بك في Laravel.', function ($msg) {
        $msg->to('mohamed202203785@gmail.com')
            ->subject('SMTP Verification Test - Gmail');
    });
    echo "SUCCESS: Email sent successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

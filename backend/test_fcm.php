<?php

/**
 * Quick FCM Test Script
 * Usage: php artisan tinker < test_fcm.php
 * Or: php test_fcm.php (standalone with bootstrap)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Utils\Services\Firebase\CloudMessaging;

// ====================================
// ⚠️ PUT YOUR FCM TOKEN HERE
// ====================================
$fcmToken = 'PASTE_YOUR_FCM_TOKEN_HERE';

if ($fcmToken === 'PASTE_YOUR_FCM_TOKEN_HERE') {
    echo "\n❌ You need to paste your FCM token first!\n";
    echo "Edit test_fcm.php and replace 'PASTE_YOUR_FCM_TOKEN_HERE' with your actual token.\n\n";
    exit(1);
}

echo "\n🔔 Testing Firebase Cloud Messaging...\n";
echo '📱 Token: '.substr($fcmToken, 0, 20)."...\n\n";

try {
    $fcm = new CloudMessaging;
    $fcm->setNotification('🧪 Test Notification', 'This is a test from the backend! If you see this, FCM is working ✅');
    $fcm->setData(['notification_type' => 'test', 'timestamp' => now()->toDateTimeString()]);
    $fcm->setTokens([$fcmToken]);
    $fcm->send();

    echo "✅ Notification sent successfully! Check your device.\n\n";
} catch (\Throwable $e) {
    echo '❌ Error: '.$e->getMessage()."\n";
    echo "📋 Full trace:\n".$e->getTraceAsString()."\n\n";
}

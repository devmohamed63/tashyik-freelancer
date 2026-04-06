<?php

use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminNewOrderMessage;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Get the latest order to have real relationships data if possible
    $order = Order::with(['customer', 'service', 'category'])->latest()->first();

    if (! $order) {
        throw new \Exception("لا توجد طلبات سابقة في قاعدة البيانات لتجربة الإيميل عليها.");
    }

    // Force send the email synchronously to the admin
    Mail::to('mohamed202203785@gmail.com')->send(new AdminNewOrderMessage($order));
    
    echo "Success! A test order email was triggered using Order #{$order->id}.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

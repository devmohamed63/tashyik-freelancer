<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = App\Models\User::find(1);
if ($admin) {
    $admin->email = 'mohamed202203785@gmail.com';
    $admin->save();
    echo "SUCCESS: Admin email updated to: " . $admin->email . "\n";
} else {
    echo "ERROR: Admin not found.\n";
}

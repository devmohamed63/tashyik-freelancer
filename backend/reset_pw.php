<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\User::find(37);
$u->password = \Illuminate\Support\Facades\Hash::make('12345678');
$u->save();
echo "Done! Phone: {$u->phone}\n";

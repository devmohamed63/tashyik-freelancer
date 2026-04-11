<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Find first institution
$institution = User::whereIn('entity_type', ['institution', 'company'])
    ->where('type', 'service-provider')
    ->first();

if (!$institution) {
    echo "No institution found!\n";
    exit(1);
}

echo "Institution: {$institution->name} (ID: {$institution->id})\n";

$categories = $institution->categories()->pluck('id')->toArray();

// Create 4 fake members
for ($i = 1; $i <= 4; $i++) {
    $member = User::create([
        'name' => "موظف تجريبي $i",
        'phone' => '05' . rand(10000000, 99999999),
        'password' => Hash::make('12345678'),
        'type' => 'service-provider',
        'entity_type' => 'individual',
        'status' => $i <= 3 ? 'active' : 'pending',
        'city_id' => $institution->city_id,
        'residence_name' => 'اسم هوية تجريبي',
        'residence_number' => '10' . rand(10000000, 99999999),
        'bank_name' => 'الراجحي',
        'iban' => 'SA' . rand(1000000000, 9999999999) . rand(1000000000, 9999999999),
    ]);

    $member->institution_id = $institution->id;
    $member->save();

    if ($categories) {
        $member->categories()->attach($categories);
    }

    echo "  Created: {$member->name} (ID: {$member->id})\n";
}

echo "\nDone! 4 members added to '{$institution->name}'\n";
echo "Password for all: 12345678\n";

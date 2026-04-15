<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$locales = ['ar', 'en', 'hi', 'bn', 'ur', 'tl', 'id', 'fr'];

echo "=== Categories with description issues ===\n";
$rows = DB::table('categories')->get(['id', 'name', 'description']);
foreach ($rows as $r) {
    $desc = $r->description;
    if (!$desc) {
        echo "ID {$r->id}: description is NULL\n";
        continue;
    }
    $decoded = json_decode($desc, true);
    if (!is_array($decoded)) {
        echo "ID {$r->id}: not JSON - " . substr($desc, 0, 50) . "\n";
        continue;
    }
    $missing = array_diff($locales, array_keys($decoded));
    $empty = [];
    foreach ($decoded as $loc => $val) {
        if (empty($val) && $val !== '0') $empty[] = $loc;
    }
    if (!empty($missing) || !empty($empty)) {
        echo "ID {$r->id}: ";
        if (!empty($missing)) echo "missing: " . implode(',', $missing) . " ";
        if (!empty($empty)) echo "empty: " . implode(',', $empty);
        echo "\n";
    }
}

echo "\n=== Services with description issues ===\n";
$rows = DB::table('services')->whereIn('id', [65, 94, 273, 274, 276])->get(['id', 'name', 'description']);
foreach ($rows as $r) {
    $nameData = json_decode($r->name, true);
    $arName = is_array($nameData) ? ($nameData['ar'] ?? '') : '';
    echo "ID {$r->id} ({$arName}): desc=" . substr($r->description ?? 'NULL', 0, 100) . "\n";
}

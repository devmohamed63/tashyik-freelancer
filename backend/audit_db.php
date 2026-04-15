<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$locales = ['ar', 'en', 'hi', 'bn', 'ur', 'tl', 'id', 'fr'];

// Define tables with their translatable columns and slug columns
$tables = [
    'categories' => ['translatable' => ['name', 'description'], 'slug' => 'slug'],
    'services' => ['translatable' => ['name', 'description'], 'slug' => 'slug'],
    'articles' => ['translatable' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description'], 'slug' => 'slug'],
    'cities' => ['translatable' => ['name'], 'slug' => null],
    'plans' => ['translatable' => ['name'], 'slug' => null],
    'banners' => ['translatable' => ['name'], 'slug' => null],
    'questions' => ['translatable' => ['title', 'answer'], 'slug' => null],
    'highlights' => ['translatable' => ['title'], 'slug' => null],
    'pages' => ['translatable' => ['name', 'body'], 'slug' => null],
    'service_collections' => ['translatable' => ['title'], 'slug' => null],
    'settings' => ['translatable' => ['name', 'description'], 'slug' => null],
];

echo "=== DATABASE AUDIT REPORT ===\n\n";

foreach ($tables as $table => $config) {
    $rows = DB::table($table)->get();
    $totalRows = $rows->count();
    echo "--- TABLE: {$table} ({$totalRows} rows) ---\n";

    // Check slugs
    if ($config['slug']) {
        $missingSlug = $rows->filter(fn($row) => empty($row->{$config['slug']}))->count();
        echo "  SLUGS: {$missingSlug} missing out of {$totalRows}\n";
        if ($missingSlug > 0) {
            $missing = $rows->filter(fn($row) => empty($row->{$config['slug']}));
            foreach ($missing as $row) {
                echo "    - ID {$row->id}: slug is empty\n";
            }
        }
    }

    // Check translations
    foreach ($config['translatable'] as $column) {
        $missingTranslations = [];
        foreach ($rows as $row) {
            $value = $row->$column ?? null;
            if (!$value) {
                $missingTranslations[] = ['id' => $row->id, 'issue' => 'Column is NULL/empty'];
                continue;
            }

            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                // Not JSON - plain string, means only default locale
                $missing = array_diff($locales, ['ar']);
                if (!empty($missing)) {
                    $missingTranslations[] = ['id' => $row->id, 'issue' => 'Plain string (not JSON), missing: ' . implode(', ', $missing)];
                }
                continue;
            }

            $existingLocales = array_keys($decoded);
            $missingLocales = array_diff($locales, $existingLocales);
            
            // Also check for empty values in existing locales
            $emptyLocales = [];
            foreach ($decoded as $loc => $val) {
                if (empty($val) && $val !== '0') {
                    $emptyLocales[] = $loc;
                }
            }
            
            if (!empty($missingLocales) || !empty($emptyLocales)) {
                $issues = [];
                if (!empty($missingLocales)) $issues[] = 'missing: ' . implode(', ', $missingLocales);
                if (!empty($emptyLocales)) $issues[] = 'empty: ' . implode(', ', $emptyLocales);
                $missingTranslations[] = ['id' => $row->id, 'issue' => implode(' | ', $issues)];
            }
        }

        if (!empty($missingTranslations)) {
            echo "  COLUMN '{$column}': " . count($missingTranslations) . " rows with issues\n";
            foreach (array_slice($missingTranslations, 0, 5) as $mt) {
                echo "    - ID {$mt['id']}: {$mt['issue']}\n";
            }
            if (count($missingTranslations) > 5) {
                echo "    ... and " . (count($missingTranslations) - 5) . " more\n";
            }
        } else {
            echo "  COLUMN '{$column}': ✓ All translations complete\n";
        }
    }

    echo "\n";
}

echo "=== END OF REPORT ===\n";

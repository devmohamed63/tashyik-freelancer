<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;

// ============================================================
// CONFIG
// ============================================================
$defaultLocale = 'ar';
$targetLocales = ['en', 'hi', 'bn', 'ur', 'tl', 'id', 'fr'];
$allLocales = array_merge([$defaultLocale], $targetLocales);
$credentialsPath = storage_path('app/private/google-service-account.json');
$batchSize = 50; // Google Translate batch limit

// ============================================================
// PHASE 1: FIX MISSING SLUGS
// ============================================================
echo "\n========================================\n";
echo "PHASE 1: GENERATING MISSING SLUGS\n";
echo "========================================\n\n";

// --- Categories ---
$categories = DB::table('categories')->whereNull('slug')->orWhere('slug', '')->get();
echo "Categories with missing slugs: {$categories->count()}\n";

foreach ($categories as $cat) {
    $nameData = json_decode($cat->name, true);
    $arName = is_array($nameData) ? ($nameData['ar'] ?? array_values($nameData)[0] ?? '') : ($cat->name ?? '');

    $slug = Str::slug($arName);
    if (empty($slug)) {
        $slug = 'cat-' . $cat->id;
    }

    // Ensure unique
    $originalSlug = $slug;
    $counter = 1;
    while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $cat->id)->exists()) {
        $slug = "{$originalSlug}-{$counter}";
        $counter++;
    }

    DB::table('categories')->where('id', $cat->id)->update(['slug' => $slug]);
    echo "  ✓ Category #{$cat->id}: {$slug}\n";
}

// --- Services ---
$services = DB::table('services')->whereNull('slug')->orWhere('slug', '')->get();
echo "\nServices with missing slugs: {$services->count()}\n";

foreach ($services as $svc) {
    $nameData = json_decode($svc->name, true);
    $arName = is_array($nameData) ? ($nameData['ar'] ?? array_values($nameData)[0] ?? '') : ($svc->name ?? '');

    $slug = Str::slug($arName);
    if (empty($slug)) {
        $slug = 'srv-' . $svc->id;
    }

    // Ensure unique
    $originalSlug = $slug;
    $counter = 1;
    while (DB::table('services')->where('slug', $slug)->where('id', '!=', $svc->id)->exists()) {
        $slug = "{$originalSlug}-{$counter}";
        $counter++;
    }

    DB::table('services')->where('id', $svc->id)->update(['slug' => $slug]);
    echo "  ✓ Service #{$svc->id}: {$slug}\n";
}

echo "\n✅ Phase 1 complete!\n";

// ============================================================
// PHASE 2: FIX MISSING TRANSLATIONS
// ============================================================
echo "\n========================================\n";
echo "PHASE 2: FIXING MISSING TRANSLATIONS\n";
echo "========================================\n\n";

// Initialize Google Translate client
$translationClient = new TranslationServiceClient([
    'credentials' => $credentialsPath,
]);

$credentials = json_decode(file_get_contents($credentialsPath), true);
$projectId = $credentials['project_id'];
$formattedParent = $translationClient->locationName($projectId, 'global');

/**
 * Translate a batch of texts from source locale to target locale
 */
function translateBatch(
    TranslationServiceClient $client,
    string $formattedParent,
    array $texts,
    string $sourceLocale,
    string $targetLocale
): array {
    $request = new TranslateTextRequest();
    $request->setParent($formattedParent);
    $request->setContents($texts);
    $request->setSourceLanguageCode($sourceLocale);
    $request->setTargetLanguageCode($targetLocale);

    $response = $client->translateText($request);
    $results = [];
    foreach ($response->getTranslations() as $translation) {
        $results[] = $translation->getTranslatedText();
    }
    return $results;
}

/**
 * Process a table's translatable columns
 */
function fixTableTranslations(
    string $table,
    array $translatableColumns,
    string $defaultLocale,
    array $targetLocales,
    array $allLocales,
    TranslationServiceClient $client,
    string $formattedParent,
    int $batchSize
): void {
    echo "--- Processing: {$table} ---\n";

    $rows = DB::table($table)->get();
    $totalFixed = 0;

    foreach ($translatableColumns as $column) {
        // Collect rows that need translation for this column
        $needsTranslation = [];

        foreach ($rows as $row) {
            $value = $row->$column ?? null;
            if (!$value) continue;

            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                // Plain string - treat as default locale value
                $decoded = [$defaultLocale => $value];
            }

            // Find source text (prefer ar, fallback to first available)
            $sourceText = $decoded[$defaultLocale] ?? null;
            $sourceLocale = $defaultLocale;

            if (empty($sourceText)) {
                // Try to find any non-empty locale as source
                foreach ($decoded as $loc => $val) {
                    if (!empty($val)) {
                        $sourceText = $val;
                        $sourceLocale = $loc;
                        break;
                    }
                }
            }

            if (empty($sourceText)) continue;

            // Find which locales are missing
            $missingLocales = [];
            foreach ($allLocales as $loc) {
                if ($loc === $sourceLocale) continue;
                if (empty($decoded[$loc])) {
                    $missingLocales[] = $loc;
                }
            }

            if (!empty($missingLocales)) {
                $needsTranslation[] = [
                    'id' => $row->id,
                    'sourceText' => $sourceText,
                    'sourceLocale' => $sourceLocale,
                    'missingLocales' => $missingLocales,
                    'existing' => $decoded,
                ];
            }
        }

        if (empty($needsTranslation)) {
            echo "  '{$column}': ✓ Already complete\n";
            continue;
        }

        echo "  '{$column}': Translating " . count($needsTranslation) . " rows...\n";

        // Group by missing locales and translate in batches per locale
        // First, get unique target locales needed
        $localesNeeded = [];
        foreach ($needsTranslation as $item) {
            foreach ($item['missingLocales'] as $loc) {
                $localesNeeded[$loc] = true;
            }
        }

        foreach (array_keys($localesNeeded) as $targetLocale) {
            // Collect texts that need this specific locale
            $textsToTranslate = [];
            $indexMap = [];

            foreach ($needsTranslation as $idx => $item) {
                if (in_array($targetLocale, $item['missingLocales'])) {
                    $textsToTranslate[] = $item['sourceText'];
                    $indexMap[] = $idx;
                }
            }

            if (empty($textsToTranslate)) continue;

            // Process in batches
            $batches = array_chunk($textsToTranslate, $batchSize);
            $batchIndexMaps = array_chunk($indexMap, $batchSize);

            foreach ($batches as $batchIdx => $batchTexts) {
                $batchMap = $batchIndexMaps[$batchIdx];

                try {
                    // Determine source locale (use the first item's source locale, they should be same)
                    $srcLocale = $needsTranslation[$batchMap[0]]['sourceLocale'];

                    // Skip if target == source
                    if ($targetLocale === $srcLocale) continue;

                    $translations = translateBatch($client, $formattedParent, $batchTexts, $srcLocale, $targetLocale);

                    foreach ($translations as $i => $translatedText) {
                        $originalIdx = $batchMap[$i];
                        $needsTranslation[$originalIdx]['existing'][$targetLocale] = $translatedText;
                    }
                } catch (\Throwable $e) {
                    echo "    ⚠ Error translating to {$targetLocale} (batch {$batchIdx}): {$e->getMessage()}\n";
                }

                // Small delay to avoid rate limiting
                usleep(100000); // 100ms
            }

            echo "    → {$targetLocale}: " . count($textsToTranslate) . " texts translated\n";
        }

        // Save back to database
        foreach ($needsTranslation as $item) {
            DB::table($table)->where('id', $item['id'])->update([
                $column => json_encode($item['existing'], JSON_UNESCAPED_UNICODE),
            ]);
            $totalFixed++;
        }
    }

    echo "  ✅ {$table}: {$totalFixed} rows updated\n\n";
}

// Process tables in order (smallest first)
$tablesToFix = [
    'settings' => ['name', 'description'],
    'service_collections' => ['title'],
    'pages' => ['name', 'body'],
    'questions' => ['title', 'answer'],
    'categories' => ['description'],  // name is already complete
    'services' => ['description'],     // name is already complete
    'cities' => ['name'],
    'articles' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description'],
    'highlights' => ['title'],
];

foreach ($tablesToFix as $table => $columns) {
    try {
        fixTableTranslations(
            $table, $columns, $defaultLocale, $targetLocales, $allLocales,
            $translationClient, $formattedParent, $batchSize
        );
    } catch (\Throwable $e) {
        echo "  ❌ Error processing {$table}: {$e->getMessage()}\n\n";
    }
}

$translationClient->close();

echo "\n========================================\n";
echo "✅ ALL PHASES COMPLETE!\n";
echo "========================================\n";
echo "Run 'php audit_db.php' to verify results.\n\n";

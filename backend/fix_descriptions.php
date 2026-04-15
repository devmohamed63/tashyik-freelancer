<?php

/**
 * Fix missing descriptions for categories (35 rows) and services (5 rows).
 *
 * Problem: These rows have a description column that is either {"ar":""} or {"fr":""}
 *          with all other locales missing. Essentially the description was never filled in.
 *
 * Strategy:
 *   - Use the Arabic name as a base to generate a short description.
 *   - Translate that description into all target locales using Google Cloud Translate.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;

// ============================================================
// CONFIG
// ============================================================
$defaultLocale = 'ar';
$targetLocales = ['en', 'hi', 'bn', 'ur', 'tl', 'id', 'fr'];
$allLocales    = array_merge([$defaultLocale], $targetLocales);
$credentialsPath = storage_path('app/private/google-service-account.json');
$batchSize = 50;

// ============================================================
// Google Translate Client
// ============================================================
$translationClient = new TranslationServiceClient([
    'credentials' => $credentialsPath,
]);

$credentials     = json_decode(file_get_contents($credentialsPath), true);
$projectId       = $credentials['project_id'];
$formattedParent = $translationClient->locationName($projectId, 'global');

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
    $results  = [];
    foreach ($response->getTranslations() as $translation) {
        $results[] = $translation->getTranslatedText();
    }
    return $results;
}

// ============================================================
// STEP 1: Fix categories descriptions
// ============================================================
echo "\n=== Fixing categories descriptions ===\n\n";

$categories = DB::table('categories')->get();
$toFix = [];

foreach ($categories as $cat) {
    $desc = $cat->description;
    if (!$desc) {
        $toFix[] = $cat;
        continue;
    }
    $decoded = json_decode($desc, true);
    if (!is_array($decoded)) {
        $toFix[] = $cat;
        continue;
    }
    // Check if all values are empty
    $hasContent = false;
    foreach ($decoded as $val) {
        if (!empty($val)) { $hasContent = true; break; }
    }
    if (!$hasContent) {
        $toFix[] = $cat;
        continue;
    }
    // Check for missing locales
    $missing = array_diff($allLocales, array_keys($decoded));
    $emptyLocs = [];
    foreach ($decoded as $loc => $val) {
        if (empty($val) && $val !== '0') $emptyLocs[] = $loc;
    }
    if (!empty($missing) || !empty($emptyLocs)) {
        $toFix[] = $cat;
    }
}

echo "Categories needing fix: " . count($toFix) . "\n";

// For each category, generate Arabic description from name, then translate
$arTexts = [];
foreach ($toFix as $cat) {
    $nameData = json_decode($cat->name, true);
    $arName = is_array($nameData) ? ($nameData['ar'] ?? array_values($nameData)[0] ?? '') : ($cat->name ?? '');

    // Check if description already has some content we can use
    $existingDesc = json_decode($cat->description ?? '{}', true);
    $hasExisting = false;
    $existingText = '';
    $existingLocale = 'ar';

    if (is_array($existingDesc)) {
        foreach ($existingDesc as $loc => $val) {
            if (!empty($val)) {
                $hasExisting = true;
                $existingText = $val;
                $existingLocale = $loc;
                break;
            }
        }
    }

    if ($hasExisting) {
        // Use existing description as source
        $arTexts[] = [
            'id' => $cat->id,
            'sourceText' => $existingText,
            'sourceLocale' => $existingLocale,
        ];
    } else {
        // Generate from name: "خدمات {name}"
        $arDesc = "خدمات " . $arName;
        $arTexts[] = [
            'id' => $cat->id,
            'sourceText' => $arDesc,
            'sourceLocale' => 'ar',
        ];
    }
}

// Translate and save
foreach ($toFix as $idx => $cat) {
    $info = $arTexts[$idx];
    $sourceText   = $info['sourceText'];
    $sourceLocale = $info['sourceLocale'];

    $translations = [$sourceLocale => $sourceText];

    foreach ($allLocales as $targetLocale) {
        if ($targetLocale === $sourceLocale) continue;

        try {
            $result = translateBatch(
                $translationClient, $formattedParent,
                [$sourceText], $sourceLocale, $targetLocale
            );
            $translations[$targetLocale] = $result[0] ?? '';
        } catch (\Throwable $e) {
            echo "  ⚠ Cat #{$cat->id} -> {$targetLocale}: {$e->getMessage()}\n";
            $translations[$targetLocale] = $sourceText; // fallback
        }
        usleep(50000); // 50ms delay
    }

    DB::table('categories')->where('id', $cat->id)->update([
        'description' => json_encode($translations, JSON_UNESCAPED_UNICODE),
    ]);

    $nameData = json_decode($cat->name, true);
    $arName = is_array($nameData) ? ($nameData['ar'] ?? '?') : '?';
    echo "  ✓ Category #{$cat->id} ({$arName})\n";
}

echo "\n✅ Categories done!\n";

// ============================================================
// STEP 2: Fix services descriptions
// ============================================================
echo "\n=== Fixing services descriptions ===\n\n";

$serviceIds = [65, 94, 273, 274, 276];
// Also find any other services with empty descriptions
$allServices = DB::table('services')->get();
$svcToFix = [];

foreach ($allServices as $svc) {
    $desc = $svc->description;
    if (!$desc) {
        $svcToFix[] = $svc;
        continue;
    }
    $decoded = json_decode($desc, true);
    if (!is_array($decoded)) {
        $svcToFix[] = $svc;
        continue;
    }
    $hasContent = false;
    foreach ($decoded as $val) {
        if (!empty($val)) { $hasContent = true; break; }
    }
    if (!$hasContent) {
        $svcToFix[] = $svc;
        continue;
    }
    $missing = array_diff($allLocales, array_keys($decoded));
    $emptyLocs = [];
    foreach ($decoded as $loc => $val) {
        if (empty($val) && $val !== '0') $emptyLocs[] = $loc;
    }
    if (!empty($missing) || !empty($emptyLocs)) {
        $svcToFix[] = $svc;
    }
}

echo "Services needing fix: " . count($svcToFix) . "\n";

foreach ($svcToFix as $svc) {
    $nameData = json_decode($svc->name, true);
    $arName = is_array($nameData) ? ($nameData['ar'] ?? array_values($nameData)[0] ?? '') : ($svc->name ?? '');

    // Check existing desc for any content
    $existingDesc = json_decode($svc->description ?? '{}', true);
    $sourceText = '';
    $sourceLocale = 'ar';

    if (is_array($existingDesc)) {
        foreach ($existingDesc as $loc => $val) {
            if (!empty($val)) {
                $sourceText = $val;
                $sourceLocale = $loc;
                break;
            }
        }
    }

    if (empty($sourceText)) {
        // Generate from name
        $sourceText = "خدمة " . $arName;
        $sourceLocale = 'ar';
    }

    $translations = [$sourceLocale => $sourceText];

    foreach ($allLocales as $targetLocale) {
        if ($targetLocale === $sourceLocale) continue;

        try {
            $result = translateBatch(
                $translationClient, $formattedParent,
                [$sourceText], $sourceLocale, $targetLocale
            );
            $translations[$targetLocale] = $result[0] ?? '';
        } catch (\Throwable $e) {
            echo "  ⚠ Service #{$svc->id} -> {$targetLocale}: {$e->getMessage()}\n";
            $translations[$targetLocale] = $sourceText;
        }
        usleep(50000);
    }

    DB::table('services')->where('id', $svc->id)->update([
        'description' => json_encode($translations, JSON_UNESCAPED_UNICODE),
    ]);

    echo "  ✓ Service #{$svc->id} ({$arName})\n";
}

$translationClient->close();

echo "\n✅ Services done!\n";
echo "\n=== ALL FIXES COMPLETE ===\n";
echo "Run 'php audit_db.php' to verify.\n";

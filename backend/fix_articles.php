<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;

$defaultLocale = 'ar';
$targetLocales = ['en', 'hi', 'bn', 'ur', 'tl', 'id', 'fr'];
$allLocales = array_merge([$defaultLocale], $targetLocales);
$credentialsPath = storage_path('app/private/google-service-account.json');

$translationClient = new TranslationServiceClient(['credentials' => $credentialsPath]);
$credentials = json_decode(file_get_contents($credentialsPath), true);
$projectId = $credentials['project_id'];
$formattedParent = $translationClient->locationName($projectId, 'global');

echo "=== Fixing long article texts (body + meta_description) ===\n\n";

$columns = ['body', 'meta_description'];
$articles = DB::table('articles')->get();

foreach ($columns as $column) {
    echo "--- Column: {$column} ---\n";
    $fixed = 0;

    foreach ($articles as $article) {
        $value = $article->$column ?? null;
        if (!$value) continue;

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            $decoded = [$defaultLocale => $value];
        }

        // Find source text
        $sourceText = $decoded[$defaultLocale] ?? null;
        $sourceLocale = $defaultLocale;
        if (empty($sourceText)) {
            foreach ($decoded as $loc => $val) {
                if (!empty($val)) { $sourceText = $val; $sourceLocale = $loc; break; }
            }
        }
        if (empty($sourceText)) continue;

        // Find missing locales
        $missingLocales = [];
        foreach ($allLocales as $loc) {
            if ($loc === $sourceLocale) continue;
            if (empty($decoded[$loc])) $missingLocales[] = $loc;
        }
        if (empty($missingLocales)) continue;

        echo "  Article #{$article->id}: ";

        // Translate ONE article at a time to avoid size limits
        foreach ($missingLocales as $targetLocale) {
            try {
                $request = new TranslateTextRequest();
                $request->setParent($formattedParent);
                $request->setContents([$sourceText]);
                $request->setSourceLanguageCode($sourceLocale);
                $request->setTargetLanguageCode($targetLocale);

                $response = $translationClient->translateText($request);
                $translations = $response->getTranslations();

                foreach ($translations as $t) {
                    $decoded[$targetLocale] = $t->getTranslatedText();
                }

                echo "{$targetLocale}✓ ";
                usleep(50000); // 50ms delay
            } catch (\Throwable $e) {
                echo "{$targetLocale}✗ ";
                // If single article is still too long, skip it
            }
        }

        DB::table('articles')->where('id', $article->id)->update([
            $column => json_encode($decoded, JSON_UNESCAPED_UNICODE),
        ]);
        $fixed++;
        echo "\n";
    }

    echo "  ✅ {$fixed} articles fixed for '{$column}'\n\n";
}

$translationClient->close();

echo "=== DONE! Run 'php audit_db.php' to verify. ===\n";

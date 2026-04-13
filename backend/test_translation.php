<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $client = new Google\Cloud\Translate\V3\Client\TranslationServiceClient(['credentials' => storage_path('app/private/google-service-account.json')]);
    $req = new Google\Cloud\Translate\V3\TranslateTextRequest();
    $req->setParent($client->locationName('second-kite-471419-n3', 'global'));
    $req->setContents(['Test message to translate']);
    $req->setSourceLanguageCode('en');
    $req->setTargetLanguageCode('ar');
    
    $resp = $client->translateText($req);
    echo "LOCAL TEST SUCCESS: " . $resp->getTranslations()[0]->getTranslatedText() . "\n";
} catch (\Throwable $e) {
    echo "LOCAL TEST FAILED: " . $e->getMessage() . "\n";
}

<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Illuminate\Support\Facades\Log;

class TranslateAttributesJob implements ShouldQueue
{
    use Queueable;

    protected string $defaultLocale;

    protected array $availableLocales = [];

    protected array $attributes = [];

    protected array $values = [];

    protected array $translatableValues = [];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $model,
        protected array $attributesToTranslate,
    ) {
        //
    }

    protected function getAttributes()
    {
        $this->attributes = $this->attributesToTranslate;
    }

    protected function getValues(): void
    {
        foreach ($this->attributes as $attribute) {
            $this->values[$attribute] = $this->model->$attribute;
        }
    }

    protected function getTranslatableValues(): void
    {
        // Replace empty string with null value
        $this->values = array_map(function ($value) {
            if (!$value) return null;

            return $value;
        }, $this->values);

        // Filter nullable values
        $this->translatableValues = array_filter($this->values, fn($val) => isset($val));
    }

    protected function translateValues(): void
    {
        $credentialsFilePath = storage_path('app/private/google-service-account.json');

        $translationServiceClient = new TranslationServiceClient([
            'credentials' => $credentialsFilePath,
        ]);

        $credentials = json_decode(
            file_get_contents($credentialsFilePath),
            true
        );

        $projectId = $credentials['project_id'];

        $formattedParent = $translationServiceClient->locationName($projectId, 'global');

        $translatable = array_values($this->translatableValues);

        if (empty($translatable)) return;

        $translatedValues = [];

        // Keep default values
        foreach ($translatable as $key => $value) {
            $translatedValues[$key][$this->defaultLocale] = $value;
        }

        try {
            // Send translation request for every locale
            foreach ($this->availableLocales as $locale) {
                $request = new TranslateTextRequest();
                $request->setParent($formattedParent);
                $request->setContents($translatable);
                $request->setSourceLanguageCode($this->defaultLocale);
                $request->setTargetLanguageCode($locale);

                $response = $translationServiceClient->translateText($request);

                $translations = $response->getTranslations();

                foreach ($translations as $key => $translation) {
                    $text = $translation->getTranslatedText();

                    $translatedValues[$key][$locale] = $text;
                }
            }
        } finally {
            $translationServiceClient->close();
        }

        // Save translations
        foreach (array_keys($this->translatableValues) as $index => $key) {
            $this->translatableValues[$key] = $translatedValues[$index];
        }
    }

    protected function updateValues()
    {
        $attributes = array_merge($this->values, $this->translatableValues);

        foreach ($attributes as $key => $value) {
            $translations = $value;

            if (!$translations) $translations = [$this->defaultLocale => null];

            $this->model->replaceTranslations($key, $translations);
        }

        $this->model->saveQuietly();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $this->defaultLocale = config('app.fallback_locale');
            $this->availableLocales = array_filter(config('app.translation_languages'), fn($locale) => $locale != $this->defaultLocale);

            $this->getAttributes();
            $this->getValues();
            $this->getTranslatableValues();
            $this->translateValues();
            $this->updateValues();
        } catch (\Throwable $th) {
            Log::error('Failed to translate attributes: ', ['exception' => $th]);
        }
    }
}

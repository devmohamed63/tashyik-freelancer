<?php

namespace App\Utils\Traits\Models;

use App\Jobs\TranslateAttributesJob;

trait HasAutoTranslations
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Skip during seeder execution
        if (app()->runningInConsole() || app()->environment('local')) {
            return;
        }

        static::saved(function ($model) {
            if (! isset($model->translatable)) return;

            $translatableAttributes = $model->translatable;
            $updatedAttributes = $model->getDirty();

            $attributesToTranslate = array_filter($translatableAttributes, fn($attribute) => array_key_exists($attribute, $updatedAttributes) && !in_array($attribute, $model->ignoreAutoTranslations ?? []));

            if (!empty($attributesToTranslate)) TranslateAttributesJob::dispatch($model, $attributesToTranslate);
        });
    }
}

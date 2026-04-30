<?php

namespace App\Models;

use App\Utils\Traits\Models\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Banner extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory,
        InteractsWithMedia,
        HasTranslations,
        HasStatus;

    const ACTIVE_STATUS = 1;

    const INACTIVE_STATUS = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'url',
        'status'
    ];

    public array $translatable = [
        'name'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('lg')
            ->performOnCollections('image')
            ->height(720)
            ->nonOptimized()
            ->format('webp');
    }

    public function getImageUrl(string $conversionName = ''): string|null
    {
        return $this->getMedia('image')
            ->first()
            ?->getUrl($conversionName);
    }
}

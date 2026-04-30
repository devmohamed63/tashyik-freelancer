<?php

namespace App\Models;

use App\Http\Resources\SettingsResource;
use App\Utils\Traits\Models\HasAutoTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Settings extends Model implements HasMedia
{
    use HasTranslations,
        HasAutoTranslations,
        InteractsWithMedia;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'snapchat_url',
        'tiktok_url',
        'phone_number',
        'whatsapp_link',
        'email',
        'ai_blog_automation_enabled',
        'ai_blog_daily_limit',
        'ai_blog_monthly_limit',
        'ai_blog_prompt',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    public array $ignoreAutoTranslations = [
        'name'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('light_mode_logo')
            ->singleFile();

        $this->addMediaCollection('dark_mode_logo')
            ->singleFile();

        $this->addMediaCollection('icon')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('lg')
            ->performOnCollections('light_mode_logo', 'dark_mode_logo', 'icon')
            ->width(500)
            ->height(500)
            ->nonOptimized()
            ->format('webp');
    }

    public function updateCachedImage($imageName)
    {
        $url = $this->getMedia($imageName)
            ->first()
            ?->getUrl('lg');

        Cache::forget($imageName);

        Cache::forever($imageName, $url);
    }

    public function updateCache()
    {
        Cache::forget('settings');

        Cache::rememberForever('settings', fn() => new SettingsResource($this));

        $this->updateCachedImage('icon');
        $this->updateCachedImage('light_mode_logo');
        $this->updateCachedImage('dark_mode_logo');
    }
}

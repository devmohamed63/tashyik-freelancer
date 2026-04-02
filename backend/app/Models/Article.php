<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Article extends Model implements HasMedia
{
    use HasFactory,
        HasTranslations,
        HasAutoTranslations,
        HasStatus,
        InteractsWithMedia;

    const AVAILABLE_STATUS_TYPES = [
        self::ACTIVE_STATUS,
        self::INACTIVE_STATUS,
    ];

    const ACTIVE_STATUS = 1;

    const INACTIVE_STATUS = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'status',
        'is_featured',
        'published_at',
    ];

    public array $translatable = [
        'title',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->getTranslation('title', 'ar'));
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    public static function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);

        if (empty($slug)) {
            $slug = Str::slug(Str::random(8));
        }

        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')
            ->performOnCollections('featured_image')
            ->width(600)
            ->height(400)
            ->nonOptimized()
            ->format('webp');

        $this->addMediaConversion('og')
            ->performOnCollections('featured_image')
            ->width(1200)
            ->height(630)
            ->nonOptimized()
            ->format('webp');

        $this->addMediaConversion('lg')
            ->performOnCollections('featured_image')
            ->width(1200)
            ->nonOptimized()
            ->format('webp');
    }

    /**
     * Get the featured image URL.
     */
    public function getImageUrl(string $conversionName = ''): string|null
    {
        return $this->getMedia('featured_image')
            ->first()
            ?->getUrl($conversionName);
    }

    /**
     * Scope a query to only include published articles.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', self::ACTIVE_STATUS)
              ->where(function ($q) {
                  $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
              });
    }

    /**
     * Scope a query to only include featured articles.
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

}

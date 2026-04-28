<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasStatus;
use App\Utils\Traits\Models\ResolvesByIdOrSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Gate;
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
        InteractsWithMedia,
        ResolvesByIdOrSlug;

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
        'meta_keywords',
        'service_id',
        'category_id',
        'generated_by_ai',
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
        'meta_keywords',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'generated_by_ai' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Copy meta_title and meta_description onto the linked service (dashboard SEO fields).
     *
     * @param  bool  $withoutAuthorization  Skip policy check (e.g. AI job has no logged-in user).
     */
    public function syncMetaToLinkedService(bool $withoutAuthorization = false): void
    {
        if (!$this->service_id) {
            return;
        }

        $service = Service::query()->find($this->service_id);

        if (!$service) {
            return;
        }

        if (!$withoutAuthorization && !Gate::allows('update', $service)) {
            return;
        }

        $locales = array_values(array_unique(array_merge(
            ['ar', 'en'],
            array_keys($this->getTranslations('meta_title')),
            array_keys($this->getTranslations('meta_description'))
        )));

        $metaTitle = [];
        $metaDescription = [];

        foreach ($locales as $locale) {
            $metaTitle[$locale] = (string) ($this->getTranslation('meta_title', $locale, false) ?? '');
            $metaDescription[$locale] = (string) ($this->getTranslation('meta_description', $locale, false) ?? '');
        }

        $service->update([
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ]);
    }

    public function getRouteKeyName()
    {
        return 'slug';
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

        static::saving(function ($article) {
            if (!empty($article->slug)) {
                // Ensure there are no spaces in the slug and words are separated by dashes
                $article->slug = trim(preg_replace('/\s+/', '-', $article->slug), '-');
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

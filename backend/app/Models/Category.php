<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasDraggableOrder;
use App\Utils\Traits\Models\HasParent;
use App\Utils\Traits\Models\ResolvesByIdOrSlug;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory,
        InteractsWithMedia,
        HasTranslations,
        HasAutoTranslations,
        HasParent,
        HasDraggableOrder,
        ResolvesByIdOrSlug;

    /**
     * Parent column name used for HasParent trait
     */
    const PARENT_COLUMN = 'category_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'item_order',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    public function maxDraggableIndex()
    {
        $query = static::query();

        if (is_null($this->category_id)) {
            $query->whereNull('category_id');
        } else {
            $query->whereNotNull('category_id');
        }

        return $query->max('item_order') ?? 0;
    }

    /**
     * Scope a query to only include categories available in user city.
     */
    #[Scope]
    protected function availableInUserCity(Builder $query, $cityId = null): void
    {
        $city_id = $cityId ?: Auth::user()?->city_id;

        if ($city_id) {
            $query->whereRelation('cities', 'id', $city_id)
                ->orWhereRelation('parent.cities', 'id', $city_id);
        }
    }

    /**
     * The service providers that belong to the category.
     */
    public function serviceProviders(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The cities that belong to the category.
     */
    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class);
    }

    /**
     * The category services.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get services of subcategories (If this is a parent category)
     */
    public function childrenServices(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Service::class, Category::class, 'category_id', 'category_id', 'id', 'id');
    }

    /**
     * Orders of this category directly
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Service::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('sm')
            ->performOnCollections('image', 'gallery')
            ->height(800)
            ->nonOptimized()
            ->format('webp');

        $this->addMediaConversion('og')
            ->performOnCollections('image')
            ->width(1200)
            ->height(630)
            ->nonOptimized()
            ->format('webp');
    }

    public function getImageUrl(string $conversionName = ''): string|null
    {
        return $this->getMedia('image')
            ->first()
            ?->getUrl($conversionName);
    }

    /**
     * Get fake rating for category
     *
     * @return string
     */
    public function getRating(): string
    {
        $rating = Cache::rememberForever("category-{$this->id}-fake-rating", function () {
            $fakeRating = rand(70, 100) / 20;

            return number_format($fakeRating, 1, thousands_separator: '.');
        });

        return $rating;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->getTranslation('name', 'ar') ?: 'cat');
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

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
}

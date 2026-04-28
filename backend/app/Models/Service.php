<?php

namespace App\Models;

use App\Traits\SyncableWithDaftra;
use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasDraggableOrder;
use App\Utils\Traits\Models\ResolvesByIdOrSlug;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Service extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    // TODO(daftra): Service-as-Product sync is not implemented yet.
    // The `daftra_id` column and SyncableWithDaftra trait are kept for a
    // future phase where each Service will be pushed to Daftra as a Product
    // (via Daftra::createProduct) so invoice items can reference it by id.
    use HasAutoTranslations,
        HasDraggableOrder,
        HasFactory,
        HasTranslations,
        InteractsWithMedia,
        ResolvesByIdOrSlug,
        SyncableWithDaftra;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'meta_title',
        'meta_description',
        'price',
        'badge',
        'item_order',
        'warranty_days',
        'daftra_id',
    ];

    public array $translatable = [
        'name',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function maxDraggableIndex()
    {
        $query = Service::query();

        return $query->max('item_order');
    }

    /**
     * Get the warranty duration for the service.
     */
    protected function warrantyDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->warranty_days) {
                    return;
                }

                $duration = CarbonInterval::days($this->warranty_days)->cascade()->forHumans();

                return __('ui.warranty_duration', ['duration' => $duration]);
            },
        );
    }

    /**
     * Check if service is available in the user's city.
     */
    protected function availableInUserCity(): Attribute
    {
        return Attribute::make(
            get: function () {
                $user = Auth::guard('sanctum')?->user();

                if (! $user) {
                    return true;
                }

                $parent = $this->category?->parent;

                if (! $parent) {
                    return null;
                }

                return $parent?->cities->contains('id', $user->city_id);
            },
        );
    }

    /**
     * Get service category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get service promotion.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get service highlights.
     */
    public function highlights(): HasMany
    {
        return $this->hasMany(Highlight::class);
    }

    /**
     * Get service orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    public function getImageUrl(string $conversionName = ''): ?string
    {
        return $this->getMedia('image')
            ->first()
            ?->getUrl($conversionName);
    }

    public function getVisitCost()
    {
        $price = (float) $this->price;

        // No visit cost
        if ($price > 0) {
            return 0;
        }

        return (float) config('app.visit_cost');
    }

    /**
     * Get product price after discount
     *
     * @param  bool  $formated  get formated response
     * @return array [original, has_discount, after_discount, discount_percintage, currency]
     */
    public function getPrice($formated = true): array
    {
        $promotion = $this->promotion;

        if ($this->price > 0) {
            switch ($promotion?->type) {
                case Promotion::PERCENTAGE_TYPE:
                    $discountValue = $this->price * ($promotion->value / 100);
                    $discountPercintage = $promotion->value;
                    break;

                case Promotion::FIXED_TYPE:
                    $discountValue = $promotion->value;
                    $discountPercintage = ($promotion->value * 100) / $this->price;
                    break;

                default:
                    $discountValue = 0;
                    $discountPercintage = 0;
                    break;
            }
        } else {
            $discountValue = 0;
            $discountPercintage = 0;
        }

        $priceAfterDiscount = $this->price - $discountValue;

        $price = [
            'original' => number_format($this->price, config('app.decimal_places')),
            'has_discount' => (bool) isset($promotion),
            'after_discount' => $priceAfterDiscount > 0 ? number_format($this->price - $discountValue, config('app.decimal_places')) : 0,
            'discount_percintage' => (int) $discountPercintage,
            'currency' => __('ui.currency'),
        ];

        if (! $formated) {
            $price['original'] = $this->price;
            $price['after_discount'] = $priceAfterDiscount > 0 ? ($this->price - $discountValue) : 0;
        }

        return $price;
    }

    /**
     * Get fake rating for service
     */
    public function getRating(): string
    {
        $rating = Cache::rememberForever("service-{$this->id}-fake-rating", function () {
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

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = static::generateUniqueSlug($service->getTranslation('name', 'ar') ?: 'srv');
            }
        });

        static::saving(function ($service) {
            if (! empty($service->slug)) {
                $service->slug = trim(preg_replace('/\s+/', '-', $service->slug), '-');
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'srv-'.time();
        }

        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

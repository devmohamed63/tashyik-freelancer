<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Order extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory,
        InteractsWithMedia;

    /**
     * Available status types
     *
     * @var array
     */
    const AVAILABLE_STATUS_TYPES = [
        self::NEW_STATUS,
        self::SERVICE_PROVIDER_ON_THE_WAY,
        self::SERVICE_PROVIDER_ARRIVED,
        self::STARTED_STATUS,
        self::COMPLETED_STATUS,
    ];

    const NEW_STATUS = 'new';

    const SERVICE_PROVIDER_ON_THE_WAY = 'service-provider-on-the-way';

    const SERVICE_PROVIDER_ARRIVED = 'service-provider-arrived';

    const STARTED_STATUS = 'started';

    const COMPLETED_STATUS = 'completed';

    /**
     * Waiting time in minutes for the user before being able to cancel the order
     *
     * @var int
     */
    const CANCEL_WAITING_TIME = 15;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'quantity',
        'visit_cost',
        'subtotal',
        'tax_rate',
        'tax',
        'coupons_total',
        'wallet_balance',
        'total',
        'status',
        'latitude',
        'longitude',
        'service_provider_notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'completed_at,' => 'immutable_datetime',
        ];
    }

    /**
     * Check if the user can cancel order.
     */
    protected function isCancelable(): Attribute
    {
        return Attribute::make(
            get: fn(): bool => $this->status == self::NEW_STATUS
                && $this->created_at <= now()->subMinutes(self::CANCEL_WAITING_TIME)
        );
    }

    /**
     * Check if the user can review the order.
     */
    protected function isReviewable(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                $customer = Auth::user();

                $customerHasReview = $this->serviceProvider?->reviews()
                    ->where('user_id', $customer?->id)
                    ->exists();

                return $this->status == self::COMPLETED_STATUS && !$customerHasReview;
            }
        );
    }

    /**
     * Scope a query to only include new orders.
     */
    #[Scope]
    protected function isNew(Builder $query): void
    {
        $query->where('status', static::NEW_STATUS)
            ->whereNull('service_provider_id');
    }

    /**
     * Scope a query to only include assigned orders.
     */
    #[Scope]
    protected function serviceProviderOnTheWay(Builder $query): void
    {
        $query->where('status', static::SERVICE_PROVIDER_ON_THE_WAY)
            ->whereNotNull('service_provider_id');
    }

    /**
     * Scope a query to only include assigned orders.
     */
    #[Scope]
    protected function serviceProviderArrived(Builder $query): void
    {
        $query->where('status', static::SERVICE_PROVIDER_ARRIVED)
            ->whereNotNull('service_provider_id');
    }

    /**
     * Scope a query to only include started orders.
     */
    #[Scope]
    protected function started(Builder $query): void
    {
        $query->where('status', static::STARTED_STATUS)
            ->whereNotNull('service_provider_id');
    }

    /**
     * Scope a query to only include completed orders.
     */
    #[Scope]
    protected function completed(Builder $query): void
    {
        $query->where('status', static::COMPLETED_STATUS)
            ->whereNotNull('service_provider_id');
    }

    /**
     * Scope a query to get orders within maximum distance to the service provider.
     */
    #[Scope]
    protected function withinMaxDistance(Builder $query): void
    {
        $serviceProvider = request()->user();

        $lat = $serviceProvider->latitude;
        $lng = $serviceProvider->longitude;
        $radius = config('app.service_provider_max_distance');

        if ($lat && $lng && !app()->environment('testing')) {
            $query->selectRaw("
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )) AS distance
                ", [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc');
        }
    }

    /**
     * Get order customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get order service provider.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }

    /**
     * Get order service.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get order category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get order address.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get order extra services.
     */
    public function orderExtras(): HasMany
    {
        return $this->hasMany(OrderExtra::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('lg')
            ->performOnCollections('images')
            ->width(1280)
            ->height(720)
            ->nonOptimized()
            ->format('webp');
    }

    function printDistance(): string|null
    {
        $serviceProvider = request()->user();

        $lat1 = $serviceProvider->latitude;
        $lon1 = $serviceProvider->longitude;

        $lat2 = $this->latitude;
        $lon2 = $this->longitude;

        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return null;

        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = fmod($earthRadius * $c, 2) == 0
            ? intval($earthRadius * $c)
            : number_format($earthRadius * $c, 1);

        $translations = [
            'ar' => "علي بعد $distance كم",
            'en' => "About $distance km away",
            'hi' => "लगभग $distance किमी दूर",
            'bn' => "$distance কিমি দূরে",
            'ur' => "تقریباً $distance کلومیٹر دور",
            'tl' => "Mga $distance km ang layo",
            'id' => "Sekitar $distance km jauhnya",
            'fr' => "À environ $distance km",
        ];

        $locale = app()->getLocale();

        return $translations[$locale];
    }

    /**
     * Get map URL of the order location
     */
    public function getLocationURL(): string|null
    {
        if (!$this->latitude || !$this->longitude) return null;

        return "https://maps.google.com/maps?q=$this->latitude,$this->longitude";
    }
}

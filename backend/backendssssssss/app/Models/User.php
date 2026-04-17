<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Utils\Traits\Models\HasStatus;
use App\Utils\Traits\Models\HasRating;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,
        HasFactory,
        Notifiable,
        SoftDeletes,
        HasRoles,
        InteractsWithMedia,
        HasStatus,
        HasRating;

    const AVAILABLE_ACCOUNT_TYPES = [
        self::USER_ACCOUNT_TYPE,
        self::SERVICE_PROVIDER_ACCOUNT_TYPE,
    ];

    const AVAILABLE_STATUS_TYPES = [
        self::PENDING_STATUS,
        self::ACTIVE_STATUS,
        self::INACTIVE_STATUS,
    ];

    const AVAILABLE_ENTITY_TYPES = [
        self::INDIVIDUAL_ENTITY_TYPE,
        self::INSTITUTION_ENTITY_TYPE,
        self::COMPANY_ENTITY_TYPE,
    ];

    // Account types start
    const USER_ACCOUNT_TYPE = 'user';

    const SERVICE_PROVIDER_ACCOUNT_TYPE = 'service-provider';
    // Account types end

    // Entity types start
    const INDIVIDUAL_ENTITY_TYPE = 'individual';

    const INSTITUTION_ENTITY_TYPE = 'institution';

    const COMPANY_ENTITY_TYPE = 'company';
    // Entity types end

    // Status types start
    const PENDING_STATUS = 'pending';

    const ACTIVE_STATUS = 'active';

    const INACTIVE_STATUS = 'inactive';
    // Status types end

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        // Basic information
        'name',
        'phone',
        'email',
        'password',
        'type',

        // More information
        'entity_type',
        'residence_name',
        'residence_number',
        'bank_name',
        'iban',
        'commercial_registration_number',
        'tax_registration_number',

        'status',
        'ui_locale',
        'fcm_token',
        'latitude',
        'longitude',
        'last_seen_at',
        'balance',
        'used_welcome_coupon',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Check if the user is an institution or company.
     */
    public function isInstitutionOrCompany(): bool
    {
        return in_array($this->entity_type, [
            self::INSTITUTION_ENTITY_TYPE,
            self::COMPANY_ENTITY_TYPE,
        ]);
    }

    /**
     * Get the total earnings for today for the service provider.
     * For institutions, aggregates earnings from all members.
     */
    protected function earningsToday(): Attribute
    {
        return Attribute::make(
            get: function () {
                $query = $this->isInstitutionOrCompany()
                    ? Order::where(function ($q) {
                        $q->where('service_provider_id', $this->id)
                          ->orWhereIn('service_provider_id', function ($sub) {
                              $sub->select('id')->from('users')
                                  ->where('institution_id', $this->id);
                          });
                    })
                    : $this->serviceProviderOrders();

                $totalEarnings = $query
                    ->completed()
                    ->whereDay('updated_at', '=', now())
                    ->sum('subtotal');

                if (!$totalEarnings) return;

                return [
                    'total' => number_format($totalEarnings, config('app.decimal_places')),
                    'currency' => __('ui.currency'),
                ];
            },
        );
    }

    /**
     * Get the total orders for today for the service provider.
     * For institutions, aggregates orders from all members.
     */
    protected function ordersToday(): Attribute
    {
        return Attribute::make(
            get: function () {
                $query = $this->isInstitutionOrCompany()
                    ? Order::where(function ($q) {
                        $q->where('service_provider_id', $this->id)
                          ->orWhereIn('service_provider_id', function ($sub) {
                              $sub->select('id')->from('users')
                                  ->where('institution_id', $this->id);
                          });
                    })
                    : $this->serviceProviderOrders();

                $totalOrders = $query
                    ->whereDay('updated_at', '=', now())
                    ->count();

                if (!$totalOrders) return;

                return $totalOrders;
            },
        );
    }

    /**
     * Get the current order for the service provider.
     */
    protected function currentOrder(): Attribute
    {
        return Attribute::make(
            get: function () {
                $currentOrder = $this->serviceProviderOrders()
                    ->whereNot('status', Order::COMPLETED_STATUS)
                    ->first(['id']);

                if (!$currentOrder) return;

                return $currentOrder->id;
            },
        );
    }

    /**
     * Scope a query to only include pending users.
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', static::PENDING_STATUS);
    }

    /**
     * Scope a query to not include users.
     */
    #[Scope]
    protected function notUser(Builder $query): void
    {
        $query->whereNot('type', static::USER_ACCOUNT_TYPE);
    }

    /**
     * Scope a query to only include users.
     */
    #[Scope]
    protected function isUser(Builder $query): void
    {
        $query->where('type', static::USER_ACCOUNT_TYPE);
    }

    /**
     * Scope a query to only include individuals.
     */
    #[Scope]
    protected function isIndividual(Builder $query): void
    {
        $query->where('entity_type', static::INDIVIDUAL_ENTITY_TYPE);
    }

    /**
     * Scope a query to only include institutions.
     */
    #[Scope]
    protected function isInstitution(Builder $query): void
    {
        $query->where('entity_type', static::INSTITUTION_ENTITY_TYPE);
    }

    /**
     * Scope a query to only include companies.
     */
    #[Scope]
    protected function isCompany(Builder $query): void
    {
        $query->where('entity_type', static::COMPANY_ENTITY_TYPE);
    }

    /**
     * Scope a query to only include service providers.
     */
    #[Scope]
    protected function isServiceProvider(Builder $query): void
    {
        $query->where('type', static::SERVICE_PROVIDER_ACCOUNT_TYPE);
    }

    /**
     * Scope a query to get service providers within maximum distance to the order.
     */
    #[Scope]
    protected function withinMaxDistance(Builder $query, $latitude, $longitude): void
    {
        $lat = $latitude;
        $lng = $longitude;
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
     * Scope a query to only include online users (seen in the last 5 minutes).
     */
    #[Scope]
    protected function isOnline(Builder $query): void
    {
        $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    /**
     * Scope a query to only include users with a known location.
     */
    #[Scope]
    protected function hasLocation(Builder $query): void
    {
        $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get user city.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get user institution.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get service provider subscription.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get service provider payout request.
     */
    public function payoutRequest(): HasOne
    {
        return $this->hasOne(PayoutRequest::class, 'service_provider_id');
    }

    /**
     * The categories that belong to the service provider.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the orders for the customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Get the invoices for the service provider.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'service_provider_id');
    }

    /**
     * Get the orders for the service provider.
     */
    public function serviceProviderOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'service_provider_id');
    }

    /**
     * Get institution members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'institution_id');
    }

    /**
     * Get user addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the default address for user
     */
    public function defaultAddress(): ?Address
    {
        return $this->addresses->where('is_default', true)->first();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();

        $this->addMediaCollection('residence_image')
            ->singleFile();

        $this->addMediaCollection('commercial_registration_image')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('sm')
            ->performOnCollections('avatar')
            ->width(150)
            ->height(150)
            ->nonOptimized()
            ->format('webp');

        $this->addMediaConversion('lg')
            ->performOnCollections('avatar')
            ->width(500)
            ->height(500)
            ->nonOptimized()
            ->format('webp');

        $this->addMediaConversion('xl')
            ->performOnCollections('residence_image', 'commercial_registration_image', 'national_address_image')
            ->width(1280)
            ->height(720)
            ->nonOptimized()
            ->format('webp');
    }

    public function getAvatarUrl(string $conversionName = ''): string|null
    {
        $picture = $this->getMedia('avatar')->first()?->getUrl($conversionName);

        return $picture ?? 'https://ui-avatars.com/api/?background=ede9fe&color=8b5cf6&name=' . Str::slug($this->name, '+');
    }

    public function authorizeLocation()
    {
        $locale = app()->getLocale();

        $translations = [
            'ar' => 'يجب تحديد موقعك',
            'en' => 'You must set your location',
            'hi' => 'आपको अपना स्थान निर्धारित करना होगा',
            'bn' => 'আপনাকে আপনার অবস্থান নির্ধারণ করতে হবে',
            'ur' => 'آپ کو اپنی لوکیشن مقرر کرنی ہوگی',
            'tl' => 'Kailangan mong itakda ang iyong lokasyon',
            'id' => 'Anda harus menetapkan lokasi Anda',
            'fr' => 'Vous devez définir votre position',
        ];

        abort_if(!$this->latitude || !$this->longitude, 422, $translations[$locale]);
    }

    /**
     * Use the user's wallet on a specified amount
     *
     * @param float $amount
     * @param bool $deduct Deduct the amount from user's balance
     *
     * @return array [required_amount, deducted_amount]
     */
    public function useWalletBalance($amount, $decrement = true): array
    {
        // Prevent the minus amount
        $amount = max(0, $amount);

        $deduct = min($this->balance, $amount);

        // Deduct the amount from the user's wallet.
        if ($decrement) $this->decrement('balance', $deduct);

        $requiredAmount = max(0, $amount - $deduct);

        return [
            'required_amount' => $requiredAmount,
            'deducted_amount' => $deduct,
        ];
    }

    public function printBalance(): string
    {
        return number_format($this->balance, config('app.decimal_places')) . ' ' . __('ui.currency');
    }

    /**
     * Send the password reset notification.
     * Overrides the default to use our custom notification that links to the frontend.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}

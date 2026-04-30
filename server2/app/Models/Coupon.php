<?php

namespace App\Models;

use App\Utils\Traits\Models\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory,
        HasStatus;

    const AVAILABLE_TYPES = [
        self::PERCENTAGE_TYPE,
        self::FIXED_TYPE
    ];

    const AVAILABLE_TARGET_TYPES = [
        self::CATEGORIES_TARGET_TYPE,
        self::SUBCATEGORIES_TARGET_TYPE,
        self::SERVICES_TARGET_TYPE,
    ];

    const CATEGORIES_TARGET_TYPE = 'categories';

    const SUBCATEGORIES_TARGET_TYPE = 'subcategories';

    const SERVICES_TARGET_TYPE = 'services';

    const PERCENTAGE_TYPE = 'percentage';

    const FIXED_TYPE = 'fixed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'welcome',
        'target',
        'type',
        'value',
        'usage_times',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'welcome' => 'boolean',
        ];
    }

    /**
     * The categories that belong to the coupon.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    /**
     * The services that belong to the coupon.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'coupon_service');
    }

    public function isValid(Service $service): bool
    {
        $user = Auth::user();

        // Check if user has used the welcome coupon
        if ($this->welcome == true && $user->used_welcome_coupon) return false;

        switch ($this->target) {
            case self::CATEGORIES_TARGET_TYPE:
                $valid = $this->categories()->where('id', $service->category->category_id)->exists();
                break;

            case self::SUBCATEGORIES_TARGET_TYPE:
                $valid = $this->categories()->where('id', $service->category_id)->exists();
                break;

            case self::SERVICES_TARGET_TYPE:
                $valid = $this->services()->where('id', $service->id)->exists();
                break;
        }

        return $valid;
    }

    public function getValue(): string
    {
        // Check if coupon type is percentage
        if ($this->type == self::PERCENTAGE_TYPE) return (int) $this->value . '%';

        return number_format($this->value, config('app.decimal_places')) . ' ' . __('ui.currency');
    }
}

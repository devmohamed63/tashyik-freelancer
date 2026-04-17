<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;

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
        'type',
        'value',
    ];

    /**
     * The services that belong to the promotion.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function getValue(): string
    {
        // Check if promotion type is percentage
        if ($this->type == self::PERCENTAGE_TYPE) return (int) $this->value . '%';

        return number_format($this->value, config('app.decimal_places')) . ' ' . __('ui.currency');
    }
}

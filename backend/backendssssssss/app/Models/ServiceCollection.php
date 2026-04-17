<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasDraggableOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class ServiceCollection extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCollectionFactory> */
    use HasFactory,
        HasTranslations,
        HasAutoTranslations,
        HasDraggableOrder;

    const AVAILABLE_TARGET_TYPES = [
        self::CATEGORIES_TARGET_TYPE,
        self::SUBCATEGORIES_TARGET_TYPE,
        self::SERVICES_TARGET_TYPE,
    ];

    const CATEGORIES_TARGET_TYPE = 'categories';

    const SUBCATEGORIES_TARGET_TYPE = 'subcategories';

    const SERVICES_TARGET_TYPE = 'services';

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
        'title',
        'item_order',
    ];

    public array $translatable = [
        'title',
    ];

    public function maxDraggableIndex()
    {
        $query = static::query();

        return $query->max('item_order') ?? 0;
    }

    /**
     * The services that belong to the coupon.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'collection_service',
            'collection_id',
            'service_id',
        );
    }
}

<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasDraggableOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory,
        HasTranslations,
        HasAutoTranslations,
        HasDraggableOrder;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'item_order',
        'latitude',
        'longitude',
    ];

    public array $translatable = [
        'name',
    ];

    public function maxDraggableIndex()
    {
        $query = static::query();

        return $query->max('item_order') ?? 0;
    }

    /**
     * Get the service providers assigned to this city.
     */
    public function serviceProviders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class)->where('type', User::SERVICE_PROVIDER_ACCOUNT_TYPE);
    }

    /**
     * Get all users (customers + providers) assigned to this city.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the categories available in this city (via pivot).
     */
    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}

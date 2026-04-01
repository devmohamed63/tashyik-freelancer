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
    ];

    public array $translatable = [
        'name',
    ];

    public function maxDraggableIndex()
    {
        $query = static::query();

        return $query->max('item_order') ?? 0;
    }
}

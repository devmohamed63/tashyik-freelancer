<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasDraggableOrder;
use Spatie\Translatable\HasTranslations;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory,
        HasTranslations,
        HasAutoTranslations,
        HasDraggableOrder;

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
        'answer',
        'item_order',
    ];

    public array $translatable = [
        'title',
        'answer',
    ];

    public function maxDraggableIndex()
    {
        $query = static::query();

        return $query->max('item_order') ?? 0;
    }
}

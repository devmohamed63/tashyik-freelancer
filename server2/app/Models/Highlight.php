<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Highlight extends Model
{
    /** @use HasFactory<\Database\Factories\HighlightFactory> */
    use HasFactory,
        HasTranslations,
        HasAutoTranslations;

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
    ];

    public array $translatable = [
        'title',
    ];
}

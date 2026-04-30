<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PlanFeature extends Model
{
    use HasTranslations, HasAutoTranslations;

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

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}

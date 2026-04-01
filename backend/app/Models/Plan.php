<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory,
        HasAutoTranslations,
        HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'target_group',
        'price',
        'duration_in_days',
    ];

    public array $translatable = [
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_in_days' => 'integer',
        ];
    }

    /**
     * Get the plan duration in months.
     */
    protected function durationInMonths(): Attribute
    {
        return Attribute::make(
            get: function () {
                $monthDays = $this->duration_in_days;

                $monthDays = $monthDays / 30;

                $monthDays = $monthDays * 28;

                $duration = CarbonInterval::days($monthDays)->cascade()->forHumans();

                return $duration;
            },
        );
    }
}

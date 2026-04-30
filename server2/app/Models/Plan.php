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
        'price',
        'badge',
        'target_group',
        'duration_in_days',
    ];

    public const BADGES = [
        'most_chosen' => 'الأكثر اختيارًا ⭐',
        'best_value' => 'الأفضل قيمة 🎯',
        'premium' => 'باقة مميزة 💎',
        'customers_choice' => 'اختيار العملاء 👑',
        'special_offer' => 'عرض مميز 🔥',
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

    /**
     * Get plan features.
     */
    public function features(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * Get plan categories.
     */
    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}

<?php

namespace App\Models;

use App\Utils\Traits\Models\HasAutoTranslations;
use App\Utils\Traits\Models\HasStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory,
        HasTranslations,
        HasAutoTranslations,
        HasStatus;

    const AVAILABLE_STATUS_TYPES = [
        self::ACTIVE_STATUS,
        self::INACTIVE_STATUS,
    ];

    const ACTIVE_STATUS = 1;

    const INACTIVE_STATUS = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'body',
        'status',
        'tag',
    ];

    public array $translatable = [
        'name',
        'body',
    ];

    /**
     * Get specific page from default app pages
     */
    public static function getDefaultPage(string $pageName)
    {
        return self::where('tag', $pageName)->firstOrFail();
    }

    /**
     * Scope a query to only include not default pages.
     */
    #[Scope]
    protected function notDefaultPages(Builder $query): void
    {
        $query->whereNull('tag');
    }
}

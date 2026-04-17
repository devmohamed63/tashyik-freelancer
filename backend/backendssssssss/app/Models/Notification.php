<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Notification extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory,
        HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'url',
        'view',
        'type',
        'data',
    ];

    public array $translatable = [
        'title',
        'description',
    ];

    /**
     * Scope a query to only include admin notifications.
     */
    #[Scope]
    protected function forAdminOnly(Builder $query): void
    {
        $query->whereNull('user_id');
    }

    /**
     * Get notification date
     */
    public function getDate(): string
    {
        return $this->created_at->isoFormat(config('app.time_format'));
    }
}

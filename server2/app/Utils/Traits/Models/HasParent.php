<?php

namespace App\Utils\Traits\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasParent
{
    /**
     * Get model parent.
     */
    public function parent(): BelongsTo
    {
        /** @disregard P1012 */
        return $this->belongsTo(static::class, static::PARENT_COLUMN);
    }

    /**
     * Get model children.
     */
    public function children(): HasMany
    {
        /** @disregard P1012 */
        return $this->hasMany(static::class, static::PARENT_COLUMN);
    }

    /**
     * Scope a query to only include parent models.
     */
    #[Scope]
    protected function isParent(Builder $query): void
    {
        /** @disregard P1012 */
        $query->whereNull(static::PARENT_COLUMN);
    }

    /**
     * Scope a query to only include children models.
     */
    #[Scope]
    protected function isChild(Builder $query): void
    {
        /** @disregard P1012 */
        $query->whereNotNull(static::PARENT_COLUMN);
    }
}

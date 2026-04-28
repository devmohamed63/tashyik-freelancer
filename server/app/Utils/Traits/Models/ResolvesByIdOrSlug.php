<?php

namespace App\Utils\Traits\Models;

/**
 * Allows route model binding to resolve by either ID (numeric) or slug (string).
 *
 * This trait enables backward compatibility for mobile apps that send IDs
 * while the web frontend uses slugs for SEO-friendly URLs.
 *
 * Requires: The model must have a `slug` column and `getRouteKeyName()` returning 'slug'.
 */
trait ResolvesByIdOrSlug
{
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->firstOrFail();
        }

        return $this->where(
            is_numeric($value) ? 'id' : $this->getRouteKeyName(),
            $value
        )->firstOrFail();
    }
}

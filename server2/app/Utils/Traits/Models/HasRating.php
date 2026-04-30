<?php

namespace App\Utils\Traits\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRating
{
    /**
     * Get all of the model's reviews.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}

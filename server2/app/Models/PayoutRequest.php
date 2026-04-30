<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    /**
     * Get payout request service provider.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }
}

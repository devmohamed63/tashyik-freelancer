<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdBroadcast extends Model
{
    protected $fillable = [
        'audience',
        'title',
        'description',
        'image_path',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return list<string>
     */
    public function audienceKeys(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->audience))));
    }
}

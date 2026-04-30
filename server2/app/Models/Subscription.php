<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    /**
     * Available status types
     *
     * @var array
     */
    const AVAILABLE_STATUS_TYPES = [
        self::ACTIVE_STATUS,
        self::INACTIVE_STATUS,
    ];

    /**
     * Active subscription status type
     *
     * @var string
     */
    const ACTIVE_STATUS = 'active';

    /**
     * Inactive subscription status type
     *
     * @var string
     */
    const INACTIVE_STATUS = 'inactive';

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
        'paid_amount',
        'starts_at',
        'ends_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_date',
            'ends_at' => 'immutable_date',
        ];
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereDate('ends_at', '>', now());
    }

    /**
     * Scope a query to only include inactive subscriptions.
     */
    #[Scope]
    protected function inactive(Builder $query): void
    {
        $query->whereDate('ends_at', '<', now());
    }

    /**
     * Get subscription plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get subscription service provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExtra extends Model
{
    /** @use HasFactory<\Database\Factories\OrderExtraFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quantity',
        'status',
        'price',
        'tax_rate',
        'tax',
        'materials',
        'wallet_balance',
        'total',
    ];

    /**
     * Available status types
     *
     * @var array
     */
    const AVAILABLE_STATUS_TYPES = [
        self::PENDING_STATUS,
        self::PAID_STATUS,
    ];

    /**
     * Pending order extra status type
     *
     * @var string
     */
    const PENDING_STATUS = 'pending';

    /**
     * Paid order extra status type
     *
     * @var string
     */
    const PAID_STATUS = 'paid';

    /**
     * Scope a query to only include paid order extras.
     */
    #[Scope]
    protected function paid(Builder $query): void
    {
        $query->where('status', static::PAID_STATUS);
    }

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the service for the order extra.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluencerCommission extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'influencer_id',
        'order_id',
        'coupon_code',
        'order_subtotal',
        'commission_rate',
        'commission_amount',
        'status',
        'payout_reference',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'order_subtotal' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

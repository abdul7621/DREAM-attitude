<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'reason', 'images', 'status', 'resolution',
        'store_credit_amount', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'store_credit_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code', 'type', 'value', 'min_subtotal', 'max_discount',
        'usage_limit', 'used_count', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_subtotal' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValidNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    protected static function booted(): void
    {
        static::created(function (Coupon $coupon) {
            if (auth()->check()) {
                AuditLog::log('coupon_created', $coupon, [], $coupon->toArray());
            }
        });

        static::updated(function (Coupon $coupon) {
            $changes = $coupon->getChanges();
            $logChanges = [];
            $logOriginal = [];
            
            foreach ($changes as $field => $val) {
                if ($field !== 'updated_at' && $field !== 'used_count') {
                    $logChanges[$field] = $val;
                    $logOriginal[$field] = $coupon->getOriginal($field);
                }
            }

            if (!empty($logChanges) && auth()->check()) {
                AuditLog::log('coupon_updated', $coupon, $logOriginal, $logChanges);
            }
        });

        static::deleted(function (Coupon $coupon) {
            if (auth()->check()) {
                AuditLog::log('coupon_deleted', $coupon, $coupon->toArray(), []);
            }
        });
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id');
    }
}

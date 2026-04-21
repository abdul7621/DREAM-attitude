<?php

namespace App\Services;

use App\Models\Coupon;
use RuntimeException;

class CouponService
{
    public function findValidByCode(string $code): ?Coupon
    {
        $c = Coupon::query()->whereRaw('UPPER(code) = ?', [mb_strtoupper($code)])->first();
        if (! $c || ! $c->isValidNow()) {
            return null;
        }

        return $c;
    }

    public function discountAmount(Coupon $coupon, string $subtotal): string
    {
        if ((float) $subtotal < (float) $coupon->min_subtotal) {
            return '0.00';
        }

        if ($coupon->type === Coupon::TYPE_PERCENT) {
            $d = round((float) $subtotal * ((float) $coupon->value / 100), 2);
            if ($coupon->max_discount !== null) {
                $d = min($d, (float) $coupon->max_discount);
            }

            return number_format($d, 2, '.', '');
        }

        return number_format(min((float) $coupon->value, (float) $subtotal), 2, '.', '');
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $c = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->first();
        if ($c) {
            // Check usage limits again with the locked row
            if (!$c->isValidNow()) {
                throw new RuntimeException(__('Coupon usage limit exceeded or expired during checkout.'));
            }
            $c->increment('used_count');
        }
    }

    public function validateForCart(?Coupon $coupon, string $subtotal): void
    {
        if (! $coupon) {
            return;
        }
        if (! $coupon->isValidNow()) {
            throw new RuntimeException(__('Coupon is not valid.'));
        }
        if ((float) $subtotal < (float) $coupon->min_subtotal) {
            throw new RuntimeException(__('Minimum order value not met for this coupon.'));
        }
    }
}

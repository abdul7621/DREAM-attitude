<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Influencer;
use App\Models\InfluencerCommission;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InfluencerService
{
    /**
     * Record referral commission when an order is created or paid with an influencer's coupon.
     */
    public function recordCommissionForOrder(Order $order): ?InfluencerCommission
    {
        if (!$order->coupon_id && !$order->coupon_code_snapshot) {
            return null;
        }

        // Find active influencer linked to this coupon
        $influencer = null;
        if ($order->coupon_id) {
            $influencer = Influencer::where('coupon_id', $order->coupon_id)
                ->where('is_active', true)
                ->first();
        }

        if (!$influencer && $order->coupon_code_snapshot) {
            $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($order->coupon_code_snapshot))])->first();
            if ($coupon) {
                $influencer = Influencer::where('coupon_id', $coupon->id)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (!$influencer) {
            return null;
        }

        // Avoid duplicate commission records for the same order
        $existing = InfluencerCommission::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $subtotal = (float) $order->subtotal;
        $commissionRate = (float) $influencer->commission_value;
        $commissionAmount = 0.00;

        if ($influencer->commission_type === Influencer::TYPE_PERCENT) {
            $commissionAmount = round(($subtotal * $commissionRate) / 100, 2);
        } else {
            $commissionAmount = min($commissionRate, $subtotal);
        }

        $commission = InfluencerCommission::create([
            'influencer_id'     => $influencer->id,
            'order_id'          => $order->id,
            'coupon_code'       => $order->coupon_code_snapshot ?: ($influencer->coupon?->code ?? 'INFLUENCER'),
            'order_subtotal'    => $subtotal,
            'commission_rate'   => $commissionRate,
            'commission_amount' => $commissionAmount,
            'status'            => ($order->order_status === Order::ORDER_STATUS_DELIVERED) ? InfluencerCommission::STATUS_APPROVED : InfluencerCommission::STATUS_PENDING,
        ]);

        Log::info('[INFLUENCER] Commission recorded', [
            'order_id' => $order->id,
            'influencer_id' => $influencer->id,
            'commission_amount' => $commissionAmount,
            'status' => $commission->status,
        ]);

        return $commission;
    }

    /**
     * Synchronize commission status when Order lifecycle changes.
     */
    public function syncOrderStatus(Order $order, string $newStatus): void
    {
        $commission = $order->influencerCommission;
        if (!$commission) {
            return;
        }

        // Don't overwrite already paid commissions
        if ($commission->status === InfluencerCommission::STATUS_PAID) {
            return;
        }

        if ($newStatus === Order::ORDER_STATUS_DELIVERED) {
            $commission->update(['status' => InfluencerCommission::STATUS_APPROVED]);
        } elseif (in_array($newStatus, [Order::ORDER_STATUS_CANCELLED, Order::ORDER_STATUS_REFUNDED])) {
            $commission->update(['status' => InfluencerCommission::STATUS_CANCELLED]);
        }
    }

    /**
     * Settle & Mark selected commissions as PAID with payout reference.
     */
    public function settlePayout(Influencer $influencer, array $commissionIds, ?string $payoutRef = null): int
    {
        return DB::transaction(function () use ($influencer, $commissionIds, $payoutRef) {
            return InfluencerCommission::where('influencer_id', $influencer->id)
                ->whereIn('id', $commissionIds)
                ->whereIn('status', [InfluencerCommission::STATUS_PENDING, InfluencerCommission::STATUS_APPROVED])
                ->update([
                    'status' => InfluencerCommission::STATUS_PAID,
                    'paid_at' => now(),
                    'payout_reference' => $payoutRef ?: ('PAYOUT-' . date('Ymd-His')),
                ]);
        });
    }
}

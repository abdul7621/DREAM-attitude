<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\StoreCreditBalance;
use App\Models\StoreCreditLedger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    /**
     * Allocate loyalty points (store credit value) based on order total.
     */
    public function allocatePoints(Order $order): void
    {
        if (!$order->user_id) {
            return; // Guests don't accrue loyalty points
        }

        // Prevent double allocation for the same order
        $exists = StoreCreditLedger::where('user_id', $order->user_id)
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'earn')
            ->exists();

        if ($exists) {
            return;
        }

        // Rule: ₹100 Spent = 1 Point = ₹1 Store Credit (1% reward rate)
        $creditAmount = floor((float) $order->grand_total / 100);
        if ($creditAmount <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $creditAmount) {
            // Update or create user balance
            $balance = StoreCreditBalance::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => 0]
            );
            $balance->increment('balance', $creditAmount);

            // Log to ledger
            StoreCreditLedger::create([
                'user_id' => $order->user_id,
                'amount' => $creditAmount,
                'type' => 'earn',
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'note' => "Earned on Order #{$order->order_number}"
            ]);
        });
    }

    /**
     * Convert points/store credits to a checkout coupon code.
     */
    public function convertPointsToCoupon(User $user, float $amount): Coupon
    {
        $balance = StoreCreditBalance::where('user_id', $user->id)->first();
        if (!$balance || $balance->balance < $amount || $amount <= 0) {
            throw new \Exception('Insufficient points balance.');
        }

        return DB::transaction(function () use ($user, $amount, $balance) {
            // Deduct points
            $balance->decrement('balance', $amount);

            // Log ledger
            StoreCreditLedger::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => 'redeem',
                'note' => "Redeemed points for checkout coupon"
            ]);

            // Create coupon
            $code = 'LOYAL-' . strtoupper(Str::random(8));
            return Coupon::create([
                'code' => $code,
                'type' => 'flat',
                'value' => $amount,
                'min_subtotal' => $amount + 1, // Require slightly larger subtotal
                'is_active' => true,
                'user_id' => $user->id, // Bound specifically to this user
                'starts_at' => now(),
                'ends_at' => now()->addDays(90) // Expire in 90 days
            ]);
        });
    }
}

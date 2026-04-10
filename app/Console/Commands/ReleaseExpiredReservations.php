<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release reserved stock for pending online orders that have expired (after 30 minutes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredOrders = Order::query()
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->where('order_status', Order::ORDER_STATUS_AWAITING_PAYMENT)
            ->where('payment_method', '!=', 'cod')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired reservations found.');
            return 0;
        }

        $releasedCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, &$releasedCount) {
                // Double check it's still pending
                $order = Order::lockForUpdate()->find($order->id);
                if (!$order || $order->payment_status !== Order::PAYMENT_STATUS_PENDING) {
                    return; // Skip if changed
                }

                // Restore stock
                $order->load('orderItems.variant');
                foreach ($order->orderItems as $item) {
                    $variant = $item->variant ?? ProductVariant::query()->find($item->product_variant_id);
                    if ($variant && $variant->track_inventory) {
                        $variant->increment('stock_qty', $item->qty);
                    }
                }

                // Mark abandoned
                $order->update([
                    'payment_status' => Order::PAYMENT_STATUS_FAILED,
                    'order_status' => Order::ORDER_STATUS_ABANDONED,
                ]);

                // Log it
                Log::info('order_abandoned', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->grand_total,
                    'reason' => 'reservation_expired',
                ]);

                $releasedCount++;
            });
        }

        $this->info("Successfully released {$releasedCount} expired order reservations.");

        return 0;
    }
}

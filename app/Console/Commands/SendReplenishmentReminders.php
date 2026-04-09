<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReplenishmentReminders extends Command
{
    protected $signature = 'retention:replenishment {--days=30 : Days since delivery}';
    protected $description = 'Send replenishment reminders for consumables delivered X days ago';

    public function handle(NotificationService $notifications)
    {
        $days = (int) $this->option('days');
        $targetDate = now()->subDays($days);

        // Find orders delivered on or before the target date where reminder isn't sent
        $orders = Order::where('order_status', Order::ORDER_STATUS_DELIVERED)
            ->whereNull('replenishment_reminder_sent_at')
            ->whereNotNull('user_id')
            ->whereHas('statusLogs', function ($q) use ($targetDate) {
                $q->where('status', Order::ORDER_STATUS_DELIVERED)
                  ->where('created_at', '<=', $targetDate);
            })
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            // Check if user has placed ANY order *after* this order's placed_at
            $hasReordered = Order::where('user_id', $order->user_id)
                ->where('placed_at', '>', $order->placed_at)
                ->exists();

            if ($hasReordered) {
                // Ignore and don't remind since they already came back
                $order->update(['replenishment_reminder_sent_at' => now()]);
                continue;
            }

            try {
                // Route to the account order view which has a Quick Reorder button
                $reorderUrl = route('account.orders.show', $order);
                $notifications->replenishmentReminder($order->toArray(), $reorderUrl);
                
                $order->update(['replenishment_reminder_sent_at' => now()]);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to send replenishment reminder for Order #{$order->order_number}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$count} replenishment reminders.");
    }
}

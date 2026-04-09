<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Review;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReviewRequests extends Command
{
    protected $signature = 'retention:review-requests {--days=7 : Days since delivery}';
    protected $description = 'Send review requests for orders delivered X days ago';

    public function handle(NotificationService $notifications)
    {
        $days = (int) $this->option('days');
        $targetDate = now()->subDays($days);

        $orders = Order::where('order_status', Order::ORDER_STATUS_DELIVERED)
            ->whereNull('review_request_sent_at')
            ->whereNotNull('user_id')
            ->whereHas('statusLogs', function ($q) use ($targetDate) {
                $q->where('status', Order::ORDER_STATUS_DELIVERED)
                  ->where('created_at', '<=', $targetDate);
            })
            ->with(['orderItems', 'user'])
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            // Check if user already reviewed any product from this order
            $productIds = $order->orderItems->pluck('product_id')->toArray();
            $hasReviewed = Review::where('user_id', $order->user_id)
                ->whereIn('product_id', $productIds)
                ->exists();

            if ($hasReviewed) {
                $order->update(['review_request_sent_at' => now()]); // Mark as sent to ignore future
                continue;
            }

            try {
                $reviewUrl = route('account.orders.show', $order); // They can review from their order page
                $notifications->reviewRequest($order->toArray(), $reviewUrl);
                
                $order->update(['review_request_sent_at' => now()]);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to send review request for Order #{$order->order_number}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$count} review requests.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnalyticsEvent;
use App\Models\ProductMetricDaily;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AggregateProductMetrics extends Command
{
    protected $signature = 'decision-engine:aggregate {--days=1 : Number of past days to aggregate}';
    protected $description = 'Aggregate raw decision engine events into daily product metrics';

    public function handle()
    {
        $days = (int) $this->option('days');
        
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $this->info("Aggregating metrics for {$date}...");
            
            $start = Carbon::parse($date)->startOfDay();
            $end = Carbon::parse($date)->endOfDay();

            // Product Views
            $views = AnalyticsEvent::whereBetween('created_at', [$start, $end])
                ->where('event_name', 'product_view')
                ->whereNotNull('product_id')
                ->selectRaw('product_id, COUNT(id) as count')
                ->groupBy('product_id')
                ->pluck('count', 'product_id');

            // Add To Carts
            $atc = AnalyticsEvent::whereBetween('created_at', [$start, $end])
                ->where('event_name', 'add_to_cart')
                ->whereNotNull('product_id')
                ->selectRaw('product_id, COUNT(id) as count')
                ->groupBy('product_id')
                ->pluck('count', 'product_id');

            // Purchases & Revenue (Assumes line-item level tracking, or session attribution)
            // For now, we will look at all products in a session that purchased
            $purchases = AnalyticsEvent::whereBetween('created_at', [$start, $end])
                ->where('event_name', 'purchase')
                ->get();
                
            $productPurchases = [];
            $productRevenue = [];

            // A more advanced system would track line_item events.
            // For MVP, we'll keep it simple: if a session reached purchase, we credit all products viewed/ATC'd in that session
            // A better way is parsing the order contents directly from the Orders table for revenue attribution.
            // Let's do that for accuracy.

            $orders = \App\Models\Order::with('orderItems')
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            foreach ($orders as $order) {
                foreach ($order->orderItems as $item) {
                    $pid = $item->product_id;
                    if (!isset($productPurchases[$pid])) {
                        $productPurchases[$pid] = 0;
                        $productRevenue[$pid] = 0;
                    }
                    $productPurchases[$pid] += $item->qty;
                    $productRevenue[$pid] += (float) $item->line_total;
                }
            }

            // Get all unique product IDs
            $allIds = collect(array_keys($views->toArray()))
                ->merge(array_keys($atc->toArray()))
                ->merge(array_keys($productPurchases))
                ->unique();

            $insertData = [];
            foreach ($allIds as $pid) {
                $insertData[] = [
                    'product_id' => $pid,
                    'date' => $date,
                    'views' => $views[$pid] ?? 0,
                    'add_to_cart' => $atc[$pid] ?? 0,
                    'checkouts' => 0, // Hard to attribute without line items in checkout, skip for now
                    'purchases' => $productPurchases[$pid] ?? 0,
                    'revenue' => $productRevenue[$pid] ?? 0,
                    'unique_visitors' => 0, // Complex distinct count, skip for MVP
                    'searches' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertData)) {
                ProductMetricDaily::upsert(
                    $insertData,
                    ['product_id', 'date'],
                    ['views', 'add_to_cart', 'checkouts', 'purchases', 'revenue', 'unique_visitors', 'searches', 'updated_at']
                );
            }
        }
        
        $this->info('Aggregation complete!');
    }
}

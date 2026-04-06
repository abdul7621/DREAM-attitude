<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProductSalesCount
{
    public function handle(OrderPlaced $event): void
    {
        foreach ($event->order->items as $item) {
            if ($item->variant && $item->variant->product) {
                $item->variant->product->increment('sales_count', $item->qty);
            }
        }
    }
}

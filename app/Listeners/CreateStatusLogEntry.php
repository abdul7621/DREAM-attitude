<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\OrderStatusLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateStatusLogEntry
{
    public function handle(OrderStatusChanged $event): void
    {
        OrderStatusLog::create([
            'order_id' => $event->order->id,
            'status' => $event->newStatus,
            'notes' => $event->notes,
            'created_by' => auth()->id()
        ]);
    }
}

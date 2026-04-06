<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogOrderAudit
{
    public function handle(OrderStatusChanged $event): void
    {
        AuditLog::log(
            'order_status_changed',
            auth()->user(),
            $event->order,
            [
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'notes' => $event->notes
            ]
        );
    }
}

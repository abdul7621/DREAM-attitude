<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;

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

        // Invalidate dashboard cache on any status change
        Cache::forget('dashboard_kpi');
    }
}


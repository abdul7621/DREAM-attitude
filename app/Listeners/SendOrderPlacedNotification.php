<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\NotificationService;

class SendOrderPlacedNotification
{
    public function __construct(private readonly NotificationService $notifier) {}

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $this->notifier->orderPlaced([
            'order_number'  => $order->order_number,
            'customer_name' => $order->customer_name,
            'grand_total'   => $order->grand_total,
            'phone'         => $order->phone,
            'email'         => $order->email,
        ]);
    }
}

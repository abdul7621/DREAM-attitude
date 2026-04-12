<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;

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

        if (app(\App\Services\SettingsService::class)->get('notify.email_order_placed', '1') === '1' && $order->email) {
            \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\OrderPlacedMail([
                'order_number'  => $order->order_number,
                'customer_name' => $order->customer_name,
                'grand_total'   => $order->grand_total,
                'payment_method'=> $order->payment_method,
                'order_status'  => $order->order_status,
            ]));
        }

        // Invalidate dashboard + homepage cache on new order
        Cache::forget('dashboard_kpi');
        Cache::forget('home_bestsellers');
    }
}


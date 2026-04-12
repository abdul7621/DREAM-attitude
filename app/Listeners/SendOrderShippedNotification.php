<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Services\NotificationService;

class SendOrderShippedNotification
{
    public function __construct(private readonly NotificationService $notifier) {}

    public function handle(OrderShipped $event): void
    {
        $order = $event->order;

        $this->notifier->orderShipped(
            [
                'order_number'  => $order->order_number,
                'customer_name' => $order->customer_name,
                'grand_total'   => $order->grand_total,
                'phone'         => $order->phone,
                'email'         => $order->email,
            ],
            $event->awb,
            $event->trackingUrl
        );

        if (app(\App\Services\SettingsService::class)->get('notify.email_order_shipped', '1') === '1' && $order->email) {
            \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\OrderShippedMail([
                'order_number'  => $order->order_number,
                'customer_name' => $order->customer_name,
                'awb_number'    => $event->awb,
                'tracking_url'  => $event->trackingUrl,
            ]));
        }
    }
}

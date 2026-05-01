<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionsApiService
{
    public function sendPurchase(Order $order): void
    {
        $ss = app(\App\Services\SettingsService::class);
        $pixelId = $ss->get('tracking.pixel_id', config('commerce.meta.pixel_id'));
        $token = $ss->get('tracking.capi_token', config('commerce.meta.capi_token'));
        if (! $pixelId || ! $token) {
            return;
        }

        $eventTime = $order->placed_at?->timestamp ?? now()->timestamp;
        $value = (float) $order->grand_total;
        $currency = $order->currency ?? 'INR';

        $contents = [];
        $contentIds = [];
        foreach ($order->orderItems as $oi) {
            $id = $oi->sku_snapshot ?: 'v'.$oi->product_variant_id;
            $contentIds[] = $id;
            $contents[] = [
                'id' => $id,
                'quantity' => $oi->qty,
                'item_price' => (float) $oi->unit_price,
            ];
        }

        $userData = [];
        if ($order->email) {
            $userData['em'] = [hash('sha256', strtolower(trim($order->email)))];
        }
        if ($order->phone) {
            $digits = preg_replace('/\D/', '', $order->phone) ?? '';
            if ($digits !== '') {
                $userData['ph'] = [hash('sha256', $digits)];
            }
        }

        if (request()->hasCookie('_fbp')) {
            $userData['fbp'] = request()->cookie('_fbp');
        }
        if (request()->hasCookie('_fbc')) {
            $userData['fbc'] = request()->cookie('_fbc');
        }

        $event = [
            'event_name' => 'Purchase',
            'event_time' => $eventTime,
            'event_id' => 'purchase-'.$order->order_number,
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => [
                'currency' => $currency,
                'value' => $value,
                'content_ids' => $contentIds,
                'contents' => $contents,
            ],
        ];

        $form = [
            'access_token' => $token,
            'data' => json_encode([$event]),
        ];

        $testCode = config('commerce.meta.capi_test_event_code');
        if ($testCode) {
            $form['test_event_code'] = $testCode;
        }

        $url = 'https://graph.facebook.com/v21.0/'.$pixelId.'/events';

        try {
            $res = Http::asForm()->post($url, $form);

            if (! $res->successful()) {
                Log::warning('Meta CAPI Purchase failed', ['body' => $res->body(), 'status' => $res->status()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI Purchase exception', ['message' => $e->getMessage()]);
        }
    }
}

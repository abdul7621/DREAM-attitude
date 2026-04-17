<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MetaConversionsApiService;
use Illuminate\View\View;

class OrderSuccessController extends Controller
{
    public function __construct(
        private readonly MetaConversionsApiService $metaCapi
    ) {}

    public function show(string $orderNumber): View
    {
        $order = Order::query()->where('order_number', $orderNumber)->with('orderItems')->firstOrFail();
        if (auth()->check()) {
            abort_unless($order->user_id === auth()->id(), 403, 'Unauthorized access to order.');
        } else {
            $sessionToken = session('guest_order_token_' . $order->order_number);
            abort_unless($sessionToken && $sessionToken === $order->guest_token, 403, 'Unauthorized access to order.');
        }

        $isFirstVisit = false;
        \Illuminate\Support\Facades\DB::transaction(function() use ($order, &$isFirstVisit) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (is_null($lockedOrder->tracking_fired_at)) {
                $lockedOrder->tracking_fired_at = now();
                $lockedOrder->save();
                $isFirstVisit = true;
            }
        });

        return view('storefront.order-success', compact('order', 'isFirstVisit'));
    }
}

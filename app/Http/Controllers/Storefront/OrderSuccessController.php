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
        
        // Fix for multiple purchase events firing on refresh
        $sessionKey = 'tracked_order_' . $order->id;
        $isFirstVisit = !session()->has($sessionKey);
        if ($isFirstVisit) {
            session()->put($sessionKey, true);
        }

        return view('storefront.order-success', compact('order', 'isFirstVisit'));
    }
}

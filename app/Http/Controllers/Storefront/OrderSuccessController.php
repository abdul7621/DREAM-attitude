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

        $sentKey = 'capi_purchase_sent_'.$order->id;
        if (! session()->get($sentKey)) {
            $this->metaCapi->sendPurchase($order);
            session()->put($sentKey, true);
        }

        return view('storefront.order-success', compact('order'));
    }
}

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
        // Meta CAPI is now handled robustly on the backend via OrderPlaced event.

        return view('storefront.order-success', compact('order'));
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function orders(): View
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('id')
            ->paginate(15);

        return view('storefront.account.orders', compact('orders'));
    }

    public function orderShow(Order $order): View
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load(['orderItems', 'shipments', 'returnRequests']);

        return view('storefront.account.order-show', compact('order'));
    }
}

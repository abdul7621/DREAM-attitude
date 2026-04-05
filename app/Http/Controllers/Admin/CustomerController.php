<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->withSum('orders', 'grand_total');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user): View
    {
        $user->load(['orders' => function ($q) {
            $q->orderByDesc('id');
        }]);

        $totalSpent = $user->orders->sum('grand_total');
        $orderCount = $user->orders->count();
        $avgOrder = $orderCount > 0 ? round($totalSpent / $orderCount, 2) : 0;

        $reviews = \App\Models\Review::where('user_id', $user->id)->with('product')->latest()->get();
        $returns = \App\Models\ReturnRequest::whereIn('order_id', $user->orders->pluck('id'))->with('order')->latest()->get();

        return view('admin.customers.show', compact('user', 'totalSpent', 'orderCount', 'avgOrder', 'reviews', 'returns'));
    }
}

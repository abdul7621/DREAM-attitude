<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReturnRequestController extends Controller
{
    public function index(): View
    {
        $returns = ReturnRequest::query()
            ->where('user_id', Auth::id())
            ->with('order')
            ->orderByDesc('id')
            ->paginate(15);

        return view('storefront.account.returns', compact('returns'));
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $data = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        // Prevent duplicate open return for same order
        $existing = ReturnRequest::query()
            ->where('order_id', $order->id)
            ->whereIn('status', ['requested', 'approved', 'received'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'A return request for this order is already open.');
        }

        ReturnRequest::query()->create([
            'order_id' => $order->id,
            'user_id'  => Auth::id(),
            'reason'   => $data['reason'],
            'status'   => 'requested',
        ]);

        return redirect()->route('account.returns')
            ->with('success', 'Return request submitted. We will respond within 48 hours.');
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    // ── Dashboard ────────────────────────────────────────────
    public function dashboard(): View
    {
        $user = Auth::user();
        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $totalOrders = Order::where('user_id', $user->id)->count();
        $totalSpent = (float) Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        return view('storefront.account.dashboard', compact(
            'user', 'recentOrders', 'totalOrders', 'totalSpent', 'wishlistCount'
        ));
    }

    // ── Orders ──────────────────────────────────────────────
    public function orders(): View
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->withCount('orderItems')
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

    // ── Profile ─────────────────────────────────────────────
    public function profile(): View
    {
        return view('storefront.account.profile', ['user' => Auth::user()]);
    }

    public function profileUpdate(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function passwordUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed.');
    }

    // ── Reorder ─────────────────────────────────────────────
    public function reorder(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('orderItems');

        $cartService = app(\App\Services\CartService::class);
        $added = 0;
        $unavailable = [];

        foreach ($order->orderItems as $item) {
            $variant = \App\Models\ProductVariant::query()
                ->where('id', $item->product_variant_id)
                ->where('is_active', true)
                ->first();

            if (!$variant) {
                $unavailable[] = $item->product_name_snapshot;
                continue;
            }

            // Check stock if tracked
            if ($variant->track_inventory && $variant->stock_qty < 1) {
                $unavailable[] = $item->product_name_snapshot;
                continue;
            }

            // Check product is still active
            $product = \App\Models\Product::query()
                ->where('id', $variant->product_id)
                ->where('status', 'active')
                ->first();

            if (!$product) {
                $unavailable[] = $item->product_name_snapshot;
                continue;
            }

            $cart = $cartService->getCart();
            \Illuminate\Support\Facades\DB::transaction(function () use ($cart, $variant) {
                $existing = $cart->items()->where('product_variant_id', $variant->id)->lockForUpdate()->first();
                if ($existing) {
                    $combined = $existing->qty + 1;
                    $newQty = (!$variant->track_inventory) ? max(1, $combined) : min($variant->stock_qty, max(1, $combined));
                    $existing->update(['qty' => $newQty]);
                } else {
                    $qty = (!$variant->track_inventory) ? 1 : min($variant->stock_qty, 1);
                    if ($qty > 0) {
                        $cart->items()->create([
                            'product_variant_id' => $variant->id,
                            'qty' => $qty,
                        ]);
                    }
                }
            });
            $added++;
        }

        $totalItems = $order->orderItems->count();

        if ($added === 0) {
            return redirect()->route('account.orders.show', $order)
                ->with('error', 'None of the items from this order are currently available.');
        }

        if (count($unavailable) > 0) {
            $names = implode(', ', array_slice($unavailable, 0, 3));
            return redirect()->route('cart.index')
                ->with('success', "{$added} of {$totalItems} items added to cart. Unavailable: {$names}.");
        }

        return redirect()->route('cart.index')
            ->with('success', "All {$added} items from order #{$order->order_number} added to cart.");
    }
}

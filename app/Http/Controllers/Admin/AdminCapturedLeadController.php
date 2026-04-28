<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCapturedLeadController extends Controller
{
    public function index(Request $request)
    {
        // We only want carts that have a guest_phone (captured lead)
        $query = Cart::with(['items.variant.product'])
            ->whereNotNull('guest_phone');

        // EXCLUDE carts that have converted
        // In this system, if an order exists with the same phone recently, it's converted.
        // We do a NOT EXISTS subquery for orders with the same phone in the last 7 days.
        $query->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('orders')
              ->whereColumn('orders.phone', 'carts.guest_phone')
              ->whereRaw('orders.placed_at >= carts.captured_at'); // Order placed AFTER capture
        });

        // Search
        if ($request->filled('search')) {
            $query->where('guest_phone', 'like', '%' . $request->search . '%');
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('lead_status', $request->status);
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'fresh') {
                $query->where('captured_at', '>=', now()->startOfDay());
            } elseif ($request->filter === 'abandoned_24h') {
                $query->where('captured_at', '<', now()->subHours(24));
            }
        }

        $query->orderBy('captured_at', 'desc');

        $leads = $query->paginate(20);

        // Calculate potential recoverable revenue
        // We calculate this for the paginated set for performance, or the whole unfiltered set.
        // Let's do a fast raw query for the total potential revenue of all "recoverable" leads
        $potentialRevenueRaw = DB::table('carts')
            ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->join('product_variants', 'cart_items.product_variant_id', '=', 'product_variants.id')
            ->whereNotNull('carts.guest_phone')
            ->whereIn('carts.lead_status', ['New', 'Contacted', 'Interested'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('orders')
                  ->whereColumn('orders.phone', 'carts.guest_phone')
                  ->whereRaw('orders.placed_at >= carts.captured_at');
            })
            ->selectRaw('SUM(product_variants.price_retail * cart_items.qty) as total')
            ->first();
            
        $potentialRevenue = $potentialRevenueRaw->total ?? 0;

        return view('admin.conversion_engine.leads', compact('leads', 'potentialRevenue'));
    }

    public function updateStatus(Request $request, Cart $cart)
    {
        $request->validate([
            'lead_status' => 'required|in:New,Contacted,Interested,Converted,Dead',
            'lead_notes' => 'nullable|string'
        ]);

        $cart->update([
            'lead_status' => $request->lead_status,
            'lead_notes' => $request->lead_notes
        ]);

        return response()->json(['success' => true]);
    }
}

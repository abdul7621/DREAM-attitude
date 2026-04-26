<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSession;
use Illuminate\Http\Request;

class AnalyticsSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalyticsSession::with('visitor')
            ->latest('started_at');

        // Optional filters
        if ($request->filled('bounce')) {
            $query->where('is_bounce', $request->boolean('bounce'));
        }
        if ($request->filled('purchase')) {
            $query->where('reached_purchase', $request->boolean('purchase'));
        }
        if ($request->filled('cart')) {
            $query->where('reached_cart', $request->boolean('cart'));
        }

        $sessions = $query->paginate(30)->withQueryString();

        return view('admin.analytics.sessions', compact('sessions'));
    }

    public function show($id)
    {
        $session = AnalyticsSession::with(['visitor.user', 'events' => function($q) {
            $q->orderBy('created_at', 'asc');
        }, 'events.product'])->findOrFail($id);

        return view('admin.analytics.timeline', compact('session'));
    }
}

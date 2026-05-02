<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSession;
use Illuminate\Http\Request;

class AnalyticsSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = AnalyticsSession::with(['visitor.user'])
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

        // Per-page selector (default 30, max 200)
        $perPage = min((int) $request->input('per_page', 30), 200);

        $sessions = $query->paginate($perPage)->withQueryString();

        return view('admin.analytics.sessions', compact('sessions', 'perPage'));
    }

    public function show($id)
    {
        $session = AnalyticsSession::with(['visitor.user', 'events' => function($q) {
            $q->orderBy('created_at', 'asc');
        }, 'events.product'])->findOrFail($id);

        return view('admin.analytics.timeline', compact('session'));
    }

    /**
     * CSV Export — all sessions with visitor details.
     */
    public function exportCsv(Request $request)
    {
        $query = AnalyticsSession::with(['visitor.user'])
            ->latest('started_at');

        // Same filters as index
        if ($request->filled('bounce')) {
            $query->where('is_bounce', $request->boolean('bounce'));
        }
        if ($request->filled('purchase')) {
            $query->where('reached_purchase', $request->boolean('purchase'));
        }
        if ($request->filled('cart')) {
            $query->where('reached_cart', $request->boolean('cart'));
        }

        $sessions = $query->limit(5000)->get();

        $filename = 'sessions_export_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sessions) {
            $out = fopen('php://output', 'w');

            // CSV Header
            fputcsv($out, [
                'Session ID',
                'Started At',
                'Duration (sec)',
                'Device',
                'Browser',
                'OS',
                'Country',
                'City',
                'Region',
                'Source',
                'Medium',
                'Campaign',
                'Landing Page',
                'Exit Page',
                'Referrer',
                'Pages',
                'Events',
                'Is Bounce',
                'Reached Product',
                'Reached Cart',
                'Reached Checkout',
                'Reached Purchase',
                'Revenue',
                'Customer Name',
                'Customer Email',
                'Customer Phone',
                'Visitor Phone (Captured)',
            ]);

            foreach ($sessions as $s) {
                $visitor = $s->visitor;
                $user = $visitor?->user;

                fputcsv($out, [
                    $s->session_uuid,
                    $s->started_at?->format('Y-m-d H:i:s'),
                    $s->duration_seconds,
                    $s->device_type,
                    $visitor?->browser ?? '',
                    $visitor?->os ?? '',
                    $visitor?->country ?? '',
                    $visitor?->city ?? '',
                    $visitor?->region ?? '',
                    $s->source ?? 'Direct',
                    $s->medium ?? '',
                    $s->campaign ?? '',
                    $s->landing_page ?? '',
                    $s->exit_page ?? '',
                    $s->referrer ?? '',
                    $s->page_count,
                    $s->event_count,
                    $s->is_bounce ? 'Yes' : 'No',
                    $s->reached_product ? 'Yes' : 'No',
                    $s->reached_cart ? 'Yes' : 'No',
                    $s->reached_checkout ? 'Yes' : 'No',
                    $s->reached_purchase ? 'Yes' : 'No',
                    $s->revenue ?? 0,
                    $user?->name ?? '',
                    $user?->email ?? '',
                    $user?->phone ?? '',
                    $visitor?->normalized_phone ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

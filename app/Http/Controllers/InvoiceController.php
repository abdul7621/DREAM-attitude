<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\SettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function adminDownload(Order $order)
    {
        $ss = app(SettingsService::class);
        $order->load('orderItems');
        $pdf = Pdf::loadView('pdf.invoice', compact('order', 'ss'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice-' . $order->id . '.pdf');
    }

    public function customerDownload(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        $ss = app(SettingsService::class);
        $order->load('orderItems');
        $pdf = Pdf::loadView('pdf.invoice', compact('order', 'ss'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice-' . $order->id . '.pdf');
    }
}

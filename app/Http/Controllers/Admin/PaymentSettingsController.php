<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        $gateways = PaymentMethod::orderBy('sort_order')->get();
        return view('admin.settings.payments', compact('gateways'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'gateways' => ['required', 'array'],
            'gateways.*.is_active' => ['nullable', 'boolean'],
            'gateways.*.config' => ['nullable', 'array'],
            'default_gateway' => ['required', 'exists:payment_methods,name'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['gateways'] as $name => $gatewayData) {
                $method = PaymentMethod::where('name', $name)->first();
                if ($method) {
                    $method->update([
                        'is_active' => $gatewayData['is_active'] ?? false,
                        'config' => array_merge((array)$method->config, $gatewayData['config'] ?? []),
                        'is_default' => ($name === $data['default_gateway']),
                    ]);
                }
            }
        });

        return back()->with('success', 'Payment gateway settings updated successfully.');
    }
}

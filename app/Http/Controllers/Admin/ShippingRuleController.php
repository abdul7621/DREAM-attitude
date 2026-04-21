<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingRuleController extends Controller
{
    public function index(): View
    {
        $rules = ShippingRule::query()->orderBy('priority')->orderByDesc('id')->paginate(30);

        return view('admin.shipping-rules.index', compact('rules'));
    }

    public function create(): View
    {
        return view('admin.shipping-rules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:flat,weight,pincode',
            'priority'  => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'config'    => 'required|array',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['priority']  = $data['priority'] ?? 0;

        if (isset($data['config']['raw_json'])) {
            $decoded = json_decode($data['config']['raw_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors(['config' => 'Invalid JSON structure provided.']);
            }
            $data['config'] = $decoded;
        }

        ShippingRule::query()->create($data);

        return redirect()->route('admin.shipping-rules.index')->with('success', 'Shipping rule created.');
    }

    public function edit(ShippingRule $shippingRule): View
    {
        return view('admin.shipping-rules.edit', compact('shippingRule'));
    }

    public function update(Request $request, ShippingRule $shippingRule): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:flat,weight,pincode',
            'priority'  => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'config'    => 'required|array',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['priority']  = $data['priority'] ?? 0;

        if (isset($data['config']['raw_json'])) {
            $decoded = json_decode($data['config']['raw_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withInput()->withErrors(['config' => 'Invalid JSON structure provided.']);
            }
            $data['config'] = $decoded;
        }

        $shippingRule->update($data);

        return redirect()->route('admin.shipping-rules.index')->with('success', 'Shipping rule updated.');
    }

    public function destroy(ShippingRule $shippingRule): RedirectResponse
    {
        $shippingRule->delete();

        return redirect()->route('admin.shipping-rules.index')->with('success', 'Shipping rule deleted.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ShippingRuleController extends Controller
{
    public function index(): View
    {
        $rules = ShippingRule::with('action')->orderByDesc('priority')->orderByDesc('id')->paginate(30);

        return view('admin.shipping-rules.index', compact('rules'));
    }

    public function create(): View
    {
        return view('admin.shipping-rules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'priority'            => 'nullable|integer|min:0',
            'is_active'           => 'boolean',
            'action_type'         => 'required|in:flat,free,percentage,per_kg',
            'action_value'        => 'required|numeric|min:0',
            'conditions'          => 'nullable|array',
            'conditions.*.type'   => 'required|string',
            'conditions.*.operator'=> 'required|string',
            'conditions.*.value'  => 'required',
        ]);

        DB::transaction(function () use ($data, $request) {
            $rule = ShippingRule::create([
                'name'      => $data['name'],
                'priority'  => $data['priority'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $rule->action()->create([
                'type'  => $data['action_type'],
                'value' => $data['action_value'],
            ]);

            if (!empty($data['conditions'])) {
                foreach ($data['conditions'] as $cond) {
                    $val = is_string($cond['value']) && str_contains($cond['value'], ',') 
                        ? array_map('trim', explode(',', $cond['value'])) 
                        : [$cond['value']];

                    $rule->conditions()->create([
                        'type'     => $cond['type'],
                        'operator' => $cond['operator'],
                        'value'    => $val, // Casts to JSON implicitly
                    ]);
                }
            }
        });

        return redirect()->route('admin.shipping-rules.index')->with('success', 'Shipping rule created.');
    }

    public function edit(ShippingRule $shippingRule): View
    {
        $shippingRule->load(['conditions', 'action']);
        return view('admin.shipping-rules.edit', compact('shippingRule'));
    }

    public function update(Request $request, ShippingRule $shippingRule): RedirectResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'priority'            => 'nullable|integer|min:0',
            'is_active'           => 'boolean',
            'action_type'         => 'required|in:flat,free,percentage,per_kg',
            'action_value'        => 'required|numeric|min:0',
            'conditions'          => 'nullable|array',
            'conditions.*.type'   => 'required_with:conditions|string',
            'conditions.*.operator'=> 'required_with:conditions|string',
            'conditions.*.value'  => 'required_with:conditions',
        ]);

        DB::transaction(function () use ($data, $request, $shippingRule) {
            $shippingRule->update([
                'name'      => $data['name'],
                'priority'  => $data['priority'] ?? 0,
                'is_active' => $request->boolean('is_active'),
            ]);

            $shippingRule->action()->updateOrCreate(
                ['rule_id' => $shippingRule->id],
                [
                    'type'  => $data['action_type'],
                    'value' => $data['action_value'],
                ]
            );

            // Re-sync conditions (easiest way to handle dynamic arrays)
            $shippingRule->conditions()->delete();
            
            if (!empty($data['conditions'])) {
                foreach ($data['conditions'] as $cond) {
                    $val = is_string($cond['value']) && str_contains($cond['value'], ',') 
                        ? array_map('trim', explode(',', $cond['value'])) 
                        : [$cond['value']];

                    $shippingRule->conditions()->create([
                        'type'     => $cond['type'],
                        'operator' => $cond['operator'],
                        'value'    => $val, // Casts to JSON implicitly
                    ]);
                }
            }
        });

        return redirect()->route('admin.shipping-rules.index')->with('success', 'Shipping rule updated.');
    }

    public function destroy(ShippingRule $shippingRule): RedirectResponse
    {
        $shippingRule->delete();

        return redirect()->route('admin.shipping-rules.index')->with('success', 'Shipping rule deleted.');
    }
}

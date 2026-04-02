<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()->orderByDesc('id')->paginate(30);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'        => 'required|string|max:64|unique:coupons,code',
            'type'        => 'required|in:flat,percent',
            'value'       => 'required|numeric|min:0',
            'min_subtotal'=> 'nullable|numeric|min:0',
            'max_discount'=> 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
            'is_active'   => 'boolean',
        ]);

        $data['code']      = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active', true);

        Coupon::query()->create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $request->validate([
            'code'        => 'required|string|max:64|unique:coupons,code,'.$coupon->id,
            'type'        => 'required|in:flat,percent',
            'value'       => 'required|numeric|min:0',
            'min_subtotal'=> 'nullable|numeric|min:0',
            'max_discount'=> 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
            'is_active'   => 'boolean',
        ]);

        $data['code']      = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }
}

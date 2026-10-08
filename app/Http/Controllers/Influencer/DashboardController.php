<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Models\Influencer;
use App\Models\InfluencerCommission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private function currentInfluencer(): Influencer
    {
        $user = Auth::user();
        $influencer = $user->influencer;

        if (!$influencer) {
            // Fallback lookup by user_id
            $influencer = Influencer::where('user_id', $user->id)->firstOrFail();
        }

        return $influencer;
    }

    public function dashboard(): View
    {
        $influencer = $this->currentInfluencer()->load(['coupon']);

        $recentCommissions = $influencer->commissions()
            ->with('order')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $stats = [
            'total_orders'    => $influencer->total_orders_count,
            'total_sales'     => $influencer->total_sales,
            'total_earned'    => $influencer->total_earned,
            'pending_payout'  => $influencer->pending_payout,
            'paid_earnings'   => $influencer->paid_earnings,
            'coupon_code'     => $influencer->coupon?->code ?? 'N/A',
            'discount_text'   => $influencer->coupon
                ? ($influencer->coupon->type === 'percent' ? $influencer->coupon->value . '% OFF' : '₹' . $influencer->coupon->value . ' FLAT OFF')
                : 'Active Discount',
            'referral_url'    => url('/?ref=' . ($influencer->coupon?->code ?? '')),
        ];

        return view('influencer.dashboard', compact('influencer', 'recentCommissions', 'stats'));
    }

    public function orders(): View
    {
        $influencer = $this->currentInfluencer();

        $commissions = $influencer->commissions()
            ->with('order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('influencer.orders', compact('influencer', 'commissions'));
    }

    public function payouts(): View
    {
        $influencer = $this->currentInfluencer();

        $payouts = $influencer->commissions()
            ->where('status', InfluencerCommission::STATUS_PAID)
            ->orderByDesc('paid_at')
            ->paginate(20);

        $pendingCommissions = $influencer->commissions()
            ->whereIn('status', [InfluencerCommission::STATUS_PENDING, InfluencerCommission::STATUS_APPROVED])
            ->get();

        return view('influencer.payouts', compact('influencer', 'payouts', 'pendingCommissions'));
    }

    public function updatePayoutSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'upi_id'             => ['nullable', 'string', 'max:128'],
            'bank_account_name'  => ['nullable', 'string', 'max:128'],
            'bank_account_no'    => ['nullable', 'string', 'max:64'],
            'bank_ifsc'          => ['nullable', 'string', 'max:32'],
            'bank_name'          => ['nullable', 'string', 'max:128'],
        ]);

        $influencer = $this->currentInfluencer();

        $bankDetails = [
            'account_name' => $data['bank_account_name'] ?? null,
            'account_no'   => $data['bank_account_no'] ?? null,
            'ifsc'         => $data['bank_ifsc'] ?? null,
            'bank_name'    => $data['bank_name'] ?? null,
        ];

        $influencer->update([
            'upi_id'       => $data['upi_id'] ?? null,
            'bank_details' => $bankDetails,
        ]);

        return back()->with('success', 'Payout settings updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', 'Your password has been changed successfully.');
    }
}

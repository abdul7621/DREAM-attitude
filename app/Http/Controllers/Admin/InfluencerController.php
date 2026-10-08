<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Influencer;
use App\Models\InfluencerCommission;
use App\Models\User;
use App\Services\InfluencerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfluencerController extends Controller
{
    public function __construct(
        private readonly InfluencerService $influencerService
    ) {}

    public function index(Request $request): View
    {
        $query = Influencer::query()
            ->with(['user', 'coupon'])
            ->withCount(['commissions as total_orders' => function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            }])
            ->withSum(['commissions as total_sales' => function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            }], 'order_subtotal')
            ->withSum(['commissions as total_earned' => function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            }], 'commission_amount')
            ->withSum(['commissions as unpaid_balance' => function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            }], 'commission_amount')
            ->orderByDesc('id');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('instagram_handle', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('coupon', function ($cq) use ($search) {
                      $cq->where('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $influencers = $query->paginate(20)->withQueryString();

        $overallStats = [
            'total_influencers' => Influencer::count(),
            'active_influencers' => Influencer::where('is_active', true)->count(),
            'total_sales_generated' => (float) InfluencerCommission::whereNotIn('status', ['cancelled'])->sum('order_subtotal'),
            'total_commission_paid' => (float) InfluencerCommission::where('status', 'paid')->sum('commission_amount'),
            'total_commission_pending' => (float) InfluencerCommission::whereIn('status', ['pending', 'approved'])->sum('commission_amount'),
        ];

        return view('admin.influencers.index', compact('influencers', 'overallStats'));
    }

    public function create(): View
    {
        // Get coupons that are active
        $coupons = Coupon::where('is_active', true)->orderByDesc('id')->get();

        return view('admin.influencers.create', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'         => ['required', 'string', 'min:6'],
            'phone'            => ['nullable', 'string', 'max:32'],
            'instagram_handle' => ['nullable', 'string', 'max:128'],
            'coupon_id'        => ['required', 'exists:coupons,id'],
            'commission_type'  => ['required', Rule::in(['percent', 'flat'])],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'upi_id'           => ['nullable', 'string', 'max:128'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $request) {
            // 1. Create or resolve User
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role'     => 'influencer',
                'is_admin' => false,
            ]);

            // 2. Create Influencer profile
            Influencer::create([
                'user_id'          => $user->id,
                'coupon_id'        => $data['coupon_id'],
                'name'             => $data['name'],
                'phone'            => $data['phone'] ?? null,
                'instagram_handle' => $data['instagram_handle'] ? ltrim($data['instagram_handle'], '@') : null,
                'commission_type'  => $data['commission_type'],
                'commission_value' => $data['commission_value'],
                'upi_id'           => $data['upi_id'] ?? null,
                'is_active'        => $request->boolean('is_active', true),
            ]);
        });

        return redirect()->route('admin.influencers.index')->with('success', 'Influencer account created successfully.');
    }

    public function show(Influencer $influencer): View
    {
        $influencer->load(['user', 'coupon']);

        $commissions = $influencer->commissions()
            ->with(['order'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.influencers.show', compact('influencer', 'commissions'));
    }

    public function edit(Influencer $influencer): View
    {
        $influencer->load(['user', 'coupon']);
        $coupons = Coupon::where('is_active', true)->orderByDesc('id')->get();

        return view('admin.influencers.edit', compact('influencer', 'coupons'));
    }

    public function update(Request $request, Influencer $influencer): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($influencer->user_id)],
            'password'         => ['nullable', 'string', 'min:6'],
            'phone'            => ['nullable', 'string', 'max:32'],
            'instagram_handle' => ['nullable', 'string', 'max:128'],
            'coupon_id'        => ['required', 'exists:coupons,id'],
            'commission_type'  => ['required', Rule::in(['percent', 'flat'])],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'upi_id'           => ['nullable', 'string', 'max:128'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $influencer, $request) {
            $userPayload = [
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];

            if (!empty($data['password'])) {
                $userPayload['password'] = Hash::make($data['password']);
            }

            $influencer->user->update($userPayload);

            $influencer->update([
                'coupon_id'        => $data['coupon_id'],
                'name'             => $data['name'],
                'phone'            => $data['phone'] ?? null,
                'instagram_handle' => $data['instagram_handle'] ? ltrim($data['instagram_handle'], '@') : null,
                'commission_type'  => $data['commission_type'],
                'commission_value' => $data['commission_value'],
                'upi_id'           => $data['upi_id'] ?? null,
                'is_active'        => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('admin.influencers.index')->with('success', 'Influencer updated successfully.');
    }

    public function settlePayout(Request $request, Influencer $influencer): RedirectResponse
    {
        $request->validate([
            'commission_ids'   => ['required', 'array'],
            'commission_ids.*' => ['exists:influencer_commissions,id'],
            'payout_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $count = $this->influencerService->settlePayout(
            $influencer,
            $request->input('commission_ids'),
            $request->input('payout_reference')
        );

        return back()->with('success', "{$count} commission records marked as Paid.");
    }

    public function destroy(Influencer $influencer): RedirectResponse
    {
        $influencer->update(['is_active' => false]);

        return redirect()->route('admin.influencers.index')->with('success', 'Influencer deactivated successfully.');
    }
}

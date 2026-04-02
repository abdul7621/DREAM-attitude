<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\StoreCreditBalance;
use App\Models\StoreCreditLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnRequestController extends Controller
{
    public function index(): View
    {
        $returns = ReturnRequest::query()
            ->with(['order', 'user'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.returns.index', compact('returns'));
    }

    public function show(ReturnRequest $returnRequest): View
    {
        $returnRequest->load(['order.orderItems', 'user']);

        return view('admin.returns.show', compact('returnRequest'));
    }

    public function update(Request $request, ReturnRequest $returnRequest): RedirectResponse
    {
        $data = $request->validate([
            'status'              => 'required|in:approved,rejected,received,closed',
            'resolution'          => 'nullable|in:refund,store_credit',
            'store_credit_amount' => 'nullable|numeric|min:0',
            'admin_notes'         => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($returnRequest, $data): void {
            $returnRequest->update([
                'status'              => $data['status'],
                'resolution'          => $data['resolution'] ?? $returnRequest->resolution,
                'store_credit_amount' => $data['store_credit_amount'] ?? $returnRequest->store_credit_amount,
                'admin_notes'         => $data['admin_notes'],
            ]);

            // Issue store credit when closing with store_credit resolution
            if (
                $data['status'] === 'closed'
                && $returnRequest->resolution === 'store_credit'
                && $returnRequest->store_credit_amount > 0
                && $returnRequest->user_id
            ) {
                $balance = StoreCreditBalance::query()->firstOrCreate(
                    ['user_id' => $returnRequest->user_id],
                    ['balance' => 0]
                );
                $balance->increment('balance', $returnRequest->store_credit_amount);

                StoreCreditLedger::query()->create([
                    'user_id'        => $returnRequest->user_id,
                    'amount'         => $returnRequest->store_credit_amount,
                    'type'           => 'credit',
                    'reference_type' => ReturnRequest::class,
                    'reference_id'   => $returnRequest->id,
                    'note'           => 'Store credit issued for return #'.$returnRequest->id,
                ]);
            }
        });

        return redirect()->route('admin.returns.show', $returnRequest)->with('success', 'Return updated.');
    }
}

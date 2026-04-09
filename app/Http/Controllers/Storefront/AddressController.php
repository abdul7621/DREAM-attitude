<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        return view('storefront.account.addresses', compact('addresses'));
    }

    public function create(): View
    {
        return view('storefront.account.address-form', ['address' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);

        // Enforce max 5 addresses
        $count = Address::where('user_id', Auth::id())->count();
        if ($count >= 5) {
            return back()->with('error', 'Maximum 5 addresses allowed. Please delete one first.')->withInput();
        }

        $data['user_id'] = Auth::id();

        // If this is set as default, unset others
        if (!empty($data['is_default'])) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        // If first address, make it default
        if ($count === 0) {
            $data['is_default'] = true;
        }

        Address::create($data);

        return redirect()->route('account.addresses.index')
            ->with('success', 'Address saved.');
    }

    public function edit(Address $address): View
    {
        abort_unless($address->user_id === Auth::id(), 403);

        return view('storefront.account.address-form', compact('address'));
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $data = $this->validateAddress($request);

        if (!empty($data['is_default'])) {
            Address::where('user_id', Auth::id())->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()->route('account.addresses.index')
            ->with('success', 'Address updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted was default, promote next one
        if ($wasDefault) {
            $next = Address::where('user_id', Auth::id())->first();
            $next?->update(['is_default' => true]);
        }

        return redirect()->route('account.addresses.index')
            ->with('success', 'Address deleted.');
    }

    public function setDefault(Address $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);

        Address::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    /**
     * API: return addresses for checkout address selector.
     */
    public function apiList(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->get(['id', 'label', 'name', 'phone', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'is_default']);

        return response()->json($addresses);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label'         => ['required', 'string', 'in:Home,Office,Other'],
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city'          => ['required', 'string', 'max:128'],
            'state'         => ['required', 'string', 'max:128'],
            'postal_code'   => ['required', 'string', 'max:10'],
            'is_default'    => ['nullable', 'boolean'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ShippingRateController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));

        $query = ShippingRate::query();
        if ($search !== '') {
            $query->where('country_code', 'like', "%{$search}%")
                  ->orWhere('region_state', 'like', "%{$search}%")
                  ->orWhere('zip_postal_code', 'like', "%{$search}%");
        }

        $rates = $query->orderBy('country_code')->orderBy('weight')->paginate(50)->appends(['search' => $search]);

        return view('admin.shipping-rates.index', compact('rates', 'search'));
    }

    public function create(): View
    {
        return view('admin.shipping-rates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country_code'    => 'required|string|size:3',
            'region_state'    => 'required|string|max:255',
            'zip_postal_code' => 'required|string|max:255',
            'weight'          => 'required|numeric|min:0',
            'price'           => 'required|numeric|min:0',
        ]);

        $data['country_code'] = strtoupper($data['country_code']);

        try {
            ShippingRate::create($data);
            return redirect()->route('admin.shipping-rates.index')->with('success', 'Shipping rate created.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Rate already exists or invalid data.'])->withInput();
        }
    }

    public function edit(ShippingRate $shippingRate): View
    {
        return view('admin.shipping-rates.edit', compact('shippingRate'));
    }

    public function update(Request $request, ShippingRate $shippingRate): RedirectResponse
    {
        $data = $request->validate([
            'country_code'    => 'required|string|size:3',
            'region_state'    => 'required|string|max:255',
            'zip_postal_code' => 'required|string|max:255',
            'weight'          => 'required|numeric|min:0',
            'price'           => 'required|numeric|min:0',
        ]);

        $data['country_code'] = strtoupper($data['country_code']);

        try {
            $shippingRate->update($data);
            return redirect()->route('admin.shipping-rates.index')->with('success', 'Shipping rate updated.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Rate update failed: duplicate rule or invalid data.'])->withInput();
        }
    }

    public function destroy(ShippingRate $shippingRate): RedirectResponse
    {
        $shippingRate->delete();
        return redirect()->route('admin.shipping-rates.index')->with('success', 'Shipping rate deleted.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Could not open CSV file.']);
        }

        // Read header
        fgetcsv($handle);

        DB::beginTransaction();
        try {
            ShippingRate::truncate();
            $records = [];
            $now = now();

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 5) {
                    continue;
                }

                $records[] = [
                    'country_code'    => strtoupper(trim($row[0])),
                    'region_state'    => trim($row[1]),
                    'zip_postal_code' => trim($row[2]),
                    'weight'          => (float) $row[3],
                    'price'           => (float) $row[4],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                if (count($records) >= 200) {
                    ShippingRate::insert($records);
                    $records = [];
                }
            }

            if (count($records) > 0) {
                ShippingRate::insert($records);
            }

            fclose($handle);
            DB::commit();

            // Save copy to project root
            $file->move(base_path(), 'tablerates.csv');

            return redirect()->route('admin.shipping-rates.index')->with('success', 'Shipping rates imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}

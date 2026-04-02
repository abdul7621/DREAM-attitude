<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $products = collect();

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $products = Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->where(function ($query) use ($like, $q): void {
                    $query->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('brand', 'like', $like);
                    if (DB::connection()->getDriverName() === 'mysql' && strlen($q) >= 3) {
                        $query->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$q]);
                    }
                })
                ->with(['variants', 'images'])
                ->orderBy('name')
                ->limit(48)
                ->get();
        }

        return view('storefront.search', compact('products', 'q'));
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
        $rows = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where(function ($query) use ($like): void {
                $query->where('name', 'like', $like)->orWhere('sku', 'like', $like);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'slug']);

        $items = $rows->map(fn (Product $p) => [
            'title' => $p->name,
            'url' => route('product.show', $p),
        ]);

        return response()->json(['items' => $items]);
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SearchSynonym;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $originalQuery = trim((string) $request->query('q', ''));
        $q = $originalQuery;

        // Apply synonym mapping if available
        $mappedQuery = null;
        if ($q !== '') {
            $synonym = SearchSynonym::where('term', strtolower($q))->first();
            if ($synonym) {
                $q = $synonym->replace_with;
                $mappedQuery = $q;
            }
        }

        $query = Product::query()->where('status', Product::STATUS_ACTIVE);

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qBuilder) use ($like, $q): void {
                $qBuilder->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('brand', 'like', $like);
                if (DB::connection()->getDriverName() === 'mysql' && strlen($q) >= 3) {
                    $qBuilder->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$q]);
                }
            });
        }

        $products = $query->with(['variants', 'images'])
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        $spellingSuggestion = null;
        $bestsellers = collect();

        // If no products found, find spelling correction using Levenshtein distance
        if ($products->isEmpty() && $q !== '') {
            $allNames = Product::where('status', Product::STATUS_ACTIVE)->pluck('name')->toArray();
            $closest = null;
            $shortest = -1;
            $qLower = strtolower($q);

            foreach ($allNames as $name) {
                $nameLower = strtolower($name);
                // Extract words from the name to check individual words too
                $words = explode(' ', $nameLower);
                foreach ($words as $word) {
                    $wordClean = preg_replace('/[^a-z0-9]/', '', $word);
                    if (strlen($wordClean) < 3) continue;

                    $lev = levenshtein($qLower, $wordClean);
                    if ($lev === 0) {
                        $closest = $name;
                        $shortest = 0;
                        break 2;
                    }
                    if ($lev <= 2 && ($shortest < 0 || $lev < $shortest)) {
                        $closest = $name;
                        $shortest = $lev;
                    }
                }

                // Fallback to checking full string similarity
                $levFull = levenshtein($qLower, $nameLower);
                if ($levFull <= 4 && ($shortest < 0 || $levFull < $shortest)) {
                    $closest = $name;
                    $shortest = $levFull;
                }
            }

            if ($closest && $shortest > 0) {
                $spellingSuggestion = $closest;
            }

            // Fetch Bestsellers for fallback display
            $bestsellers = Product::where('status', Product::STATUS_ACTIVE)
                ->where('is_bestseller', true)
                ->with(['variants', 'images'])
                ->limit(8)
                ->get();
            
            if ($bestsellers->isEmpty()) {
                $bestsellers = Product::where('status', Product::STATUS_ACTIVE)
                    ->with(['variants', 'images'])
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get();
            }
        }

        return view('storefront.search', compact('products', 'originalQuery', 'q', 'mappedQuery', 'spellingSuggestion', 'bestsellers'));
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        // Apply synonym mapping in suggestion too
        $synonym = SearchSynonym::where('term', strtolower($q))->first();
        if ($synonym) {
            $q = $synonym->replace_with;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
        $rows = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where(function ($query) use ($like): void {
                $query->where('name', 'like', $like)->orWhere('sku', 'like', $like);
            })
            ->with(['variants', 'images'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        $items = $rows->map(function (Product $p) {
            $firstImage = $p->images->first();
            $imageUrl = $firstImage ? asset('storage/' . $firstImage->image_path) : asset('images/placeholder.png');
            $priceRetail = $p->variants->first()?->price_retail ?? 0;
            $comparePrice = $p->variants->first()?->compare_at_price;

            return [
                'title' => $p->name,
                'url' => route('product.show', $p),
                'price' => '₹' . number_format($priceRetail, 2),
                'compare_price' => $comparePrice ? '₹' . number_format($comparePrice, 2) : null,
                'image' => $imageUrl,
            ];
        });

        return response()->json(['items' => $items]);
    }
}

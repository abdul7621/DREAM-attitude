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
        
        // If query is empty, return dynamic active categories & featured products
        if (strlen($q) < 1) {
            $categories = \App\Models\Category::where('is_active', true)->limit(6)->get(['id', 'name', 'slug']);
            $bestsellers = Product::where('status', Product::STATUS_ACTIVE)
                ->where('is_bestseller', true)
                ->with(['variants', 'images'])
                ->limit(4)
                ->get();
            if ($bestsellers->isEmpty()) {
                $bestsellers = Product::where('status', Product::STATUS_ACTIVE)
                    ->with(['variants', 'images'])
                    ->limit(4)
                    ->get();
            }

            $items = collect();
            foreach ($categories as $cat) {
                $items->push([
                    'title' => $cat->name,
                    'type'  => 'category',
                    'url'   => route('category.show', $cat->slug),
                ]);
            }
            $this->formatProductsResponse($bestsellers, $items);

            return response()->json(['items' => $items, 'is_default' => true]);
        }

        // Apply synonym mapping in suggestion
        $synonym = SearchSynonym::where('term', strtolower($q))->first();
        if ($synonym) {
            $q = $synonym->replace_with;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
        $items = collect();

        // 1. Matches in Brands
        $matchedBrands = \App\Models\Brand::where('is_active', true)
            ->where('name', 'like', $like)
            ->limit(2)
            ->get();
        foreach ($matchedBrands as $brand) {
            $items->push([
                'title' => $brand->name,
                'type'  => 'brand',
                'url'   => route('brand.show', $brand->slug),
                'image' => $brand->logo ? asset('storage/' . $brand->logo) : null,
            ]);
        }

        // 2. Matches in Categories
        $matchedCategories = \App\Models\Category::where('is_active', true)
            ->where('name', 'like', $like)
            ->limit(3)
            ->get();
        foreach ($matchedCategories as $cat) {
            $items->push([
                'title' => $cat->name,
                'type'  => 'category',
                'url'   => route('category.show', $cat->slug),
            ]);
        }

        // 3. Matches in Products (name, sku, brand, short_description, tags)
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where(function ($query) use ($like): void {
                $query->where('name', 'like', $like)
                      ->orWhere('sku', 'like', $like)
                      ->orWhere('brand', 'like', $like)
                      ->orWhere('short_description', 'like', $like);
            })
            ->with(['variants', 'images'])
            ->limit(6)
            ->get();

        // Fuzzy Fallback if exact LIKE matches are 0
        if ($products->isEmpty() && strlen($q) >= 2) {
            $words = preg_split('/[^a-zA-Z0-9]+/', strtolower($q));
            $firstWord = $words[0] ?? $q;
            
            $fuzzyLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $firstWord).'%';
            $products = Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->where(function ($query) use ($fuzzyLike, $firstWord): void {
                    $query->where('name', 'like', $fuzzyLike)
                          ->orWhere('brand', 'like', $fuzzyLike);
                    if (DB::connection()->getDriverName() === 'mysql' && strlen($firstWord) >= 3) {
                        $query->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$firstWord]);
                    }
                })
                ->with(['variants', 'images'])
                ->limit(6)
                ->get();
        }

        // Bestsellers fallback if products still empty
        $isFallback = false;
        if ($products->isEmpty()) {
            $isFallback = true;
            $products = Product::where('status', Product::STATUS_ACTIVE)
                ->where('is_bestseller', true)
                ->with(['variants', 'images'])
                ->limit(4)
                ->get();
            if ($products->isEmpty()) {
                $products = Product::where('status', Product::STATUS_ACTIVE)
                    ->with(['variants', 'images'])
                    ->limit(4)
                    ->get();
            }
        }

        $this->formatProductsResponse($products, $items);

        return response()->json(['items' => $items, 'is_fallback' => $isFallback]);
    }

    private function formatProductsResponse($products, $items): void
    {
        $pricing = app(\App\Services\PricingService::class);
        $currencySvc = app(\App\Services\CurrencyService::class);

        foreach ($products as $p) {
            $variant = $p->variants->firstWhere('is_active', true) ?? $p->variants->first();
            $img = $p->images->firstWhere('is_primary', true) ?? $p->images->first();

            $price = 0;
            $compare = null;
            $discount = 0;
            if ($variant) {
                $price = $pricing ? $pricing->unitPriceForCustomer($variant, auth()->user(), 1) : ($variant->price_retail ?? 0);
                $compare = $variant->compare_at_price;
                if ($compare && $compare > $price) {
                    $discount = round((($compare - $price) / $compare) * 100);
                }
            }

            $isOutOfStock = !$p->isActive() || ($variant && $variant->track_inventory && $variant->stock_qty <= 0);

            $items->push([
                'id' => $p->id,
                'title' => $p->name,
                'type' => 'product',
                'url' => route('product.show', $p),
                'image' => $img ? asset('storage/' . ($img->image_path ?? $img->path)) : null,
                'price' => $price > 0 ? ($currencySvc ? $currencySvc->format($price) : '₹' . number_format($price, 2)) : 'Price on request',
                'compare_price' => ($compare && $compare > $price) ? ($currencySvc ? $currencySvc->format($compare) : '₹' . number_format($compare, 2)) : null,
                'discount' => $discount,
                'in_stock' => !$isOutOfStock,
                'variant_title' => ($variant && $variant->title !== 'Default Title') ? $variant->title : null,
            ]);
        }
    }
}

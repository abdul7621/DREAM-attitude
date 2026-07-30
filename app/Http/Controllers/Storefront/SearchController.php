<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SearchSynonym;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $rawQ = trim((string) $request->query('q', ''));
        $q = $this->preprocessQuery($rawQ);
        $suggestion = null;

        // Collect matching category IDs for category name/slug matches
        $matchedCategoryIds = collect();
        if ($q !== '') {
            $matchingCategories = \App\Models\Category::where('is_active', true)
                ->where(function ($catQuery) use ($q): void {
                    $qLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
                    $catQuery->where('name', 'like', $qLike)
                             ->orWhere('slug', 'like', $qLike);
                })
                ->get();

            foreach ($matchingCategories as $mCat) {
                $matchedCategoryIds->push($mCat->id);
                $childIds = $mCat->children()->where('is_active', true)->pluck('id');
                if ($childIds->isNotEmpty()) {
                    $matchedCategoryIds = $matchedCategoryIds->merge($childIds);
                }
            }
            $matchedCategoryIds = $matchedCategoryIds->unique()->filter();
        }

        $query = Product::query()->where('status', Product::STATUS_ACTIVE);

        if ($q !== '') {
            $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', $q)));
            $query->where(function ($qBuilder) use ($words, $q, $matchedCategoryIds): void {
                if ($matchedCategoryIds->isNotEmpty()) {
                    $qBuilder->orWhereIn('category_id', $matchedCategoryIds);
                }

                if (!empty($words)) {
                    $qBuilder->orWhere(function ($phraseBuilder) use ($words): void {
                        foreach ($words as $word) {
                            $wordLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $word).'%';
                            $phraseBuilder->where(function ($subBuilder) use ($wordLike): void {
                                $subBuilder->where('name', 'like', $wordLike)
                                    ->orWhere('sku', 'like', $wordLike)
                                    ->orWhere('short_description', 'like', $wordLike)
                                    ->orWhere('description', 'like', $wordLike)
                                    ->orWhere('brand', 'like', $wordLike)
                                    ->orWhere('tags', 'like', $wordLike)
                                    ->orWhereHas('category', function($cQ) use ($wordLike) {
                                        $cQ->where('name', 'like', $wordLike);
                                    });
                            });
                        }
                    });
                }

                if (DB::connection()->getDriverName() === 'mysql' && strlen($q) >= 3) {
                    $qBuilder->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$q]);
                }
            });
        }

        // Apply Sorting
        switch ($request->query('sort')) {
            case 'price_asc':
                $query->orderBy(
                    \App\Models\ProductVariant::select('price_retail')
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price_retail')
                        ->limit(1),
                    'asc'
                );
                break;
            case 'price_desc':
                $query->orderByDesc(
                    \App\Models\ProductVariant::select('price_retail')
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price_retail')
                        ->limit(1)
                );
                break;
            case 'bestseller':
                $query->orderByDesc('is_bestseller')->latest();
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query
            ->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
            ->with(['variants', 'images'])
            ->paginate(24)
            ->withQueryString();

        // If no products found, try Levenshtein spell correction
        if ($products->total() === 0 && strlen($q) >= 3) {
            $correctedQ = $this->getSpellingCorrection($q);
            if ($correctedQ && $correctedQ !== $q) {
                $suggestion = $correctedQ;
                
                // Re-run query with corrected spelling
                $correctedQuery = Product::query()->where('status', Product::STATUS_ACTIVE);
                $correctedWords = array_filter(explode(' ', preg_replace('/\s+/', ' ', $correctedQ)));
                if (!empty($correctedWords)) {
                    $correctedQuery->where(function ($qBuilder) use ($correctedWords, $correctedQ): void {
                        foreach ($correctedWords as $word) {
                            $wordLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $word).'%';
                            $qBuilder->where(function ($subBuilder) use ($wordLike): void {
                                $subBuilder->where('name', 'like', $wordLike)
                                    ->orWhere('sku', 'like', $wordLike)
                                    ->orWhere('short_description', 'like', $wordLike)
                                    ->orWhere('description', 'like', $wordLike)
                                    ->orWhere('brand', 'like', $wordLike)
                                    ->orWhere('tags', 'like', $wordLike);
                            });
                        }
                        if (DB::connection()->getDriverName() === 'mysql' && strlen($correctedQ) >= 3) {
                            $qBuilder->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$correctedQ]);
                        }
                    });
                }

                switch ($request->query('sort')) {
                    case 'price_asc':
                        $correctedQuery->orderBy(
                            \App\Models\ProductVariant::select('price_retail')
                                ->whereColumn('product_id', 'products.id')
                                ->where('is_active', true)
                                ->orderBy('price_retail')
                                ->limit(1),
                            'asc'
                        );
                        break;
                    case 'price_desc':
                        $correctedQuery->orderByDesc(
                            \App\Models\ProductVariant::select('price_retail')
                                ->whereColumn('product_id', 'products.id')
                                ->where('is_active', true)
                                ->orderBy('price_retail')
                                ->limit(1)
                        );
                        break;
                    case 'bestseller':
                        $correctedQuery->orderByDesc('is_bestseller')->latest();
                        break;
                    default:
                        $correctedQuery->latest();
                        break;
                }

                $products = $correctedQuery
                    ->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
                    ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
                    ->with(['variants', 'images'])
                    ->paginate(24)
                    ->withQueryString();
            }
        }

        // Bestsellers fallback for No-Result page
        $bestsellers = collect();
        if ($products->total() === 0) {
            $bestsellers = Product::where('status', Product::STATUS_ACTIVE)
                ->where('is_bestseller', true)
                ->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
                ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
                ->with(['variants', 'images'])
                ->take(4)
                ->get();
            if ($bestsellers->isEmpty()) {
                $bestsellers = Product::where('status', Product::STATUS_ACTIVE)
                    ->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
                    ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
                    ->with(['variants', 'images'])
                    ->take(4)
                    ->get();
            }
        }

        // Get dynamic popular categories / tags instead of hardcoding
        $popularTags = Cache::remember('search_popular_tags', 3600, function() {
            return \App\Models\Category::where('is_active', true)
                ->limit(6)
                ->get(['id', 'name', 'slug']);
        });

        return view('storefront.search', compact('products', 'q', 'rawQ', 'suggestion', 'bestsellers', 'popularTags'));
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 1) {
            return response()->json(['items' => []]);
        }

        // Apply synonyms to the suggestion query
        $processedQuery = $this->preprocessQuery($q);
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $processedQuery).'%';

        $items = collect();

        // 1. Matches in Categories (limit 4)
        $matchedCategories = \App\Models\Category::where('is_active', true)
            ->where('name', 'like', $like)
            ->limit(4)
            ->get();
        foreach ($matchedCategories as $cat) {
            $items->push([
                'title' => $cat->name,
                'type'  => 'category',
                'url'   => route('category.show', $cat->slug),
            ]);
        }

        // 2. Matches in Products (limit 8)
        $suggestWords = array_filter(explode(' ', preg_replace('/\s+/', ' ', $processedQuery)));
        $productsQuery = Product::query()->where('status', Product::STATUS_ACTIVE);

        if (!empty($suggestWords)) {
            $productsQuery->where(function ($qBuilder) use ($suggestWords): void {
                foreach ($suggestWords as $word) {
                    $wordLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $word).'%';
                    $qBuilder->where(function ($subBuilder) use ($wordLike): void {
                        $subBuilder->where('name', 'like', $wordLike)
                            ->orWhere('sku', 'like', $wordLike)
                            ->orWhere('short_description', 'like', $wordLike);
                    });
                }
            });
        }

        $products = $productsQuery
            ->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
            ->with(['variants', 'images'])
            ->limit(8)
            ->get();

        $pricing = app(\App\Services\PricingService::class);

        foreach ($products as $p) {
            $variant = $p->variants->firstWhere('is_active', true) ?? $p->variants->first();
            $img = $p->images->firstWhere('is_primary', true) ?? $p->images->first();

            $price = 0;
            $compare = null;
            $discount = 0;
            if ($variant) {
                $price = $pricing ? $pricing->unitPriceForCustomer($variant, auth()->user(), 1) : ($variant->price_retail ?? 0);
                $compare = $variant->compare_at_price ?? null;
                if ($compare && $compare > $price) {
                    $discount = round((($compare - $price) / $compare) * 100);
                }
            }

            $isOutOfStock = !$p->isActive() || ($variant && $variant->track_inventory && $variant->stock_qty <= 0);

            // Avg rating from approved reviews
            $avgRating = round((float) ($p->reviews_avg_rating ?? 0), 1);
            $ratingCount = (int) ($p->reviews_count ?? 0);

            $items->push([
                'id'            => $p->id,
                'title'         => $p->name,
                'type'          => 'product',
                'url'           => route('product.show', $p),
                'image'         => $img ? asset('storage/' . ($img->image_path ?? $img->path)) : null,
                'price'         => $price > 0 ? ('₹' . number_format((float) $price, 0)) : 'Price on request',
                'compare_price' => ($compare && $compare > $price) ? ('₹' . number_format((float) $compare, 0)) : null,
                'discount'      => $discount,
                'in_stock'      => !$isOutOfStock,
                'variant_title' => ($variant && $variant->title !== 'Default Title') ? $variant->title : null,
                'rating'        => $avgRating,
                'rating_count'  => $ratingCount,
            ]);
        }

        return response()->json(['items' => $items]);
    }

    private function preprocessQuery(string $q): string
    {
        $q = strtolower(trim($q));
        if ($q === '') return '';

        try {
            // Load synonyms — 10 min cache, ordered by term length DESC so longer terms match first
            $synonyms = Cache::remember('search_synonyms_list', 600, function () {
                return SearchSynonym::query()
                    ->orderByRaw('LENGTH(term) DESC')
                    ->get(['term', 'replace_with']);
            });

            foreach ($synonyms as $synonym) {
                $term = strtolower(trim($synonym->term));
                $replace = strtolower(trim($synonym->replace_with));
                
                if (str_contains($q, $term)) {
                    $q = str_replace($term, $replace, $q);
                }
            }
        } catch (\Throwable $e) {
            // Fallback if table doesn't exist yet
        }

        return $q;
    }

    private function getSpellingCorrection(string $q): ?string
    {
        try {
            $words = Cache::remember('catalog_words', 1440, function() {
                $names = Product::where('status', Product::STATUS_ACTIVE)->pluck('name');
                $allWords = [];
                foreach ($names as $name) {
                    $tokens = preg_split('/[^a-zA-Z0-9]+/', strtolower($name));
                    foreach ($tokens as $token) {
                        if (strlen($token) >= 3) {
                            $allWords[$token] = true;
                        }
                    }
                }
                return array_keys($allWords);
            });

            if (empty($words)) return null;

            $inputWords = explode(' ', $q);
            $correctedWords = [];
            $hasCorrection = false;

            foreach ($inputWords as $word) {
                if (strlen($word) < 3) {
                    $correctedWords[] = $word;
                    continue;
                }

                if (in_array($word, $words)) {
                    $correctedWords[] = $word;
                    continue;
                }

                $closest = null;
                $shortestDist = 3;

                foreach ($words as $catalogWord) {
                    $dist = @levenshtein($word, $catalogWord);
                    if ($dist !== false && $dist < $shortestDist) {
                        $closest = $catalogWord;
                        $shortestDist = $dist;
                    }
                }

                if ($closest) {
                    $correctedWords[] = $closest;
                    $hasCorrection = true;
                } else {
                    $correctedWords[] = $word;
                }
            }

            return $hasCorrection ? implode(' ', $correctedWords) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PricingService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly PricingService $pricing
    ) {}

    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load(['variants', 'images', 'category']);

        $variantPrices = [];
        foreach ($product->variants as $v) {
            $variantPrices[$v->id] = [
                'retail' => $v->price_retail,
                'display' => $this->pricing->unitPriceForCustomer($v, auth()->user(), 1),
                'compare' => $this->pricing->compareAt($v),
            ];
        }

        return view('storefront.product', compact('product', 'variantPrices'));
    }
}

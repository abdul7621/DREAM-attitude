<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\User;

class PricingService
{
    public function __construct(
        private readonly SettingsService $settings
    ) {}

    /**
     * Resolved sale unit price for storefront (string decimal for display/cart).
     */
    public function unitPriceForCustomer(ProductVariant $variant, ?User $user, int $qty = 1): string
    {
        $basePrice = (float) (($user && $user->role === 'reseller' && (float) $variant->price_reseller > 0) 
            ? $variant->price_reseller 
            : $variant->price_retail);

        // 1. Check strict volume pricing (bundles) from product meta
        $product = $variant->product;
        if ($product) {
            $volumePricingRaw = $product->meta['volume_pricing'] ?? null;
            if (!empty($volumePricingRaw)) {
                $volumePricing = is_string($volumePricingRaw) ? json_decode($volumePricingRaw, true) : $volumePricingRaw;
                if (is_array($volumePricing)) {
                    $bestDiscount = 0;
                    foreach ($volumePricing as $bundle) {
                        $bQty = (int) ($bundle['qty'] ?? 1);
                        $bDisc = (float) ($bundle['discount_pct'] ?? 0);
                        if ($qty >= $bQty && $bDisc > $bestDiscount) {
                            $bestDiscount = $bDisc;
                        }
                    }
                    if ($bestDiscount > 0) {
                        $discounted = $basePrice * (1 - $bestDiscount / 100);
                        return number_format($discounted, 2, '.', '');
                    }
                }
            }
        }

        // 2. Fallback to older Settings-based bulk discount if no dynamic bundle matched
        $bulkMin = (int) $this->settings->get('pricing.bulk_min_qty',
            (int) config('commerce.pricing.bulk_min_qty', 0));

        if ($bulkMin > 0 && $qty >= $bulkMin && (float) $variant->price_bulk > 0) {
            return (string) $variant->price_bulk;
        }

        return (string) $basePrice;
    }

    public function compareAt(ProductVariant $variant): ?string
    {
        return $variant->compare_at_price !== null
            ? (string) $variant->compare_at_price
            : null;
    }
}

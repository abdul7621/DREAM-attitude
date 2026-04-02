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
        $bulkMin = (int) $this->settings->get('pricing.bulk_min_qty',
            (int) config('commerce.pricing.bulk_min_qty', 0));

        if ($bulkMin > 0 && $qty >= $bulkMin && $variant->price_bulk !== null) {
            return (string) $variant->price_bulk;
        }

        if ($user && $user->role === 'reseller' && $variant->price_reseller !== null) {
            return (string) $variant->price_reseller;
        }

        return (string) $variant->price_retail;
    }

    public function compareAt(ProductVariant $variant): ?string
    {
        return $variant->compare_at_price !== null
            ? (string) $variant->compare_at_price
            : null;
    }
}

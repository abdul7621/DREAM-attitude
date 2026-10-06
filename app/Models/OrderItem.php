<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name_snapshot',
        'variant_title_snapshot',
        'sku_snapshot',
        'qty',
        'unit_price',
        'mrp_snapshot',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'decimal:2',
            'mrp_snapshot' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Resolve product/variant thumbnail URL with fallbacks.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        // 1. Variant specific image
        $variantImage = $this->variant?->images?->first();
        if ($variantImage) {
            return asset('storage/' . $variantImage->path);
        }

        // 2. Product primary image
        $primaryImage = $this->product?->primaryImage();
        if ($primaryImage) {
            return asset('storage/' . $primaryImage->path);
        }

        return null;
    }

    /**
     * Resolve MRP (Maximum Retail Price / Compare-At Price).
     */
    public function getMrpAttribute(): float
    {
        if (isset($this->attributes['mrp_snapshot']) && (float) $this->attributes['mrp_snapshot'] > 0) {
            return (float) $this->attributes['mrp_snapshot'];
        }

        $variant = $this->variant;
        if ($variant && (float) $variant->compare_at_price > 0) {
            return (float) $variant->compare_at_price;
        }

        return (float) $this->unit_price;
    }

    /**
     * Intelligent volume / size detector to eliminate "Default Title" confusion.
     * Extracts "100 ML", "50 ML", etc. from variant or product name.
     */
    public function getVolumeBadgeAttribute(): ?string
    {
        // Priority 1: Meaningful variant title (not 'Default Title')
        $title = trim($this->variant_title_snapshot ?? '');
        if ($title !== '' && !in_array(strtolower($title), ['default', 'default title', 'default-title'])) {
            return $title;
        }

        // Priority 2: Variant option attributes (e.g. option1 = '100ml')
        if ($this->variant?->option1 && !in_array(strtolower($this->variant->option1), ['default', 'default title'])) {
            return $this->variant->option1;
        }

        // Priority 3: Regex volume extraction from Product Name (e.g. "Hair Serum 100ml")
        $fullName = $this->product_name_snapshot ?? '';
        if (preg_match('/\b(\d+(?:\.\d+)?\s*(?:ml|g|gm|kg|ltr|l|oz|pack|pcs))\b/i', $fullName, $matches)) {
            return strtoupper($matches[1]);
        }

        // Priority 4: Regex on SKU
        $sku = $this->sku_snapshot ?? '';
        if (preg_match('/(?:_|-)?(\d+(?:\.\d+)?(?:ml|g|gm|kg|l))\b/i', $sku, $matches)) {
            return strtoupper($matches[1]);
        }

        // Priority 5: Weight in grams
        if ($this->variant && $this->variant->weight_grams > 0) {
            return $this->variant->weight_grams . 'g';
        }

        return null;
    }

    /**
     * Calculate discount percentage from MRP.
     */
    public function getDiscountPercentAttribute(): int
    {
        $mrp = $this->mrp;
        $unitPrice = (float) $this->unit_price;

        if ($mrp > $unitPrice && $mrp > 0) {
            return (int) round((($mrp - $unitPrice) / $mrp) * 100);
        }

        return 0;
    }

    /**
     * Calculate savings per unit.
     */
    public function getSavingsAmountAttribute(): float
    {
        $mrp = $this->mrp;
        $unitPrice = (float) $this->unit_price;

        return max(0.00, round($mrp - $unitPrice, 2));
    }
}

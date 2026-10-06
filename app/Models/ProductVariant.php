<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'sku',
        'barcode',
        'option1',
        'option2',
        'option3',
        'price_retail',
        'price_reseller',
        'price_bulk',
        'compare_at_price',
        'track_inventory',
        'stock_qty',
        'weight_grams',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_retail' => 'decimal:2',
            'price_reseller' => 'decimal:2',
            'price_bulk' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (ProductVariant $variant) {
            $changes = $variant->getChanges();
            $watched = ['price_retail', 'price_reseller', 'stock_qty', 'is_active'];
            
            $logChanges = [];
            $logOriginal = [];

            foreach ($watched as $field) {
                if (array_key_exists($field, $changes)) {
                    $logChanges[$field] = $changes[$field];
                    $logOriginal[$field] = $variant->getOriginal($field);
                }
            }

            if (!empty($logChanges) && auth()->check()) {
                AuditLog::log('variant_updated', $variant, $logOriginal, $logChanges);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inStock(int $qty = 1): bool
    {
        if (! $this->track_inventory) {
            return true;
        }

        return $this->stock_qty >= $qty;
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    /**
     * Resolve variant thumbnail with product fallback.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $img = $this->images->first();
        if ($img) {
            return asset('storage/' . $img->path);
        }

        $prodImg = $this->product?->primaryImage();
        if ($prodImg) {
            return asset('storage/' . $prodImg->path);
        }

        return null;
    }

    /**
     * Resolve variant volume/size badge.
     */
    public function getVolumeBadgeAttribute(): ?string
    {
        $title = trim($this->title ?? '');
        if ($title !== '' && !in_array(strtolower($title), ['default', 'default title', 'default-title'])) {
            return $title;
        }

        if ($this->option1 && !in_array(strtolower($this->option1), ['default', 'default title'])) {
            return $this->option1;
        }

        if ($this->product && preg_match('/\b(\d+(?:\.\d+)?\s*(?:ml|g|gm|kg|ltr|l|oz|pack|pcs))\b/i', $this->product->name, $matches)) {
            return strtoupper($matches[1]);
        }

        if ($this->weight_grams > 0) {
            return $this->weight_grams . 'g';
        }

        return null;
    }

    /**
     * Resolve MRP.
     */
    public function getMrpAttribute(): float
    {
        if ($this->compare_at_price && (float) $this->compare_at_price > 0) {
            return (float) $this->compare_at_price;
        }

        return (float) $this->price_retail;
    }

    /**
     * Calculate discount percentage from MRP.
     */
    public function getDiscountPercentAttribute(): int
    {
        $mrp = $this->mrp;
        $price = (float) $this->price_retail;

        if ($mrp > $price && $mrp > 0) {
            return (int) round((($mrp - $price) / $mrp) * 100);
        }

        return 0;
    }
}

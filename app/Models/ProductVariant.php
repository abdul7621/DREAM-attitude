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
}

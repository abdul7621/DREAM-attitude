<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMetricDaily extends Model
{
    protected $table = 'product_metrics_daily';

    protected $fillable = [
        'product_id',
        'date',
        'views',
        'add_to_cart',
        'checkouts',
        'purchases',
        'revenue',
        'unique_visitors',
        'searches',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

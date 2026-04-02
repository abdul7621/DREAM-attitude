<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'reviewer_name', 'email', 'rating', 'body', 'images',
        'is_approved', 'verified_purchase',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_approved' => 'boolean',
            'verified_purchase' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

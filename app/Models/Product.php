<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'brand',
        'tags',
        'status',
        'is_featured',
        'is_bestseller',
        'sales_count',
        'seo_title',
        'seo_description',
        'meta',
        'layout_config',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'meta' => 'json',
            'layout_config' => 'json',
        ];
    }

    protected static function booted(): void
    {
        $flush = function () {
            \Illuminate\Support\Facades\Cache::forget('home_featured');
            \Illuminate\Support\Facades\Cache::forget('home_bestsellers');
            \Illuminate\Support\Facades\Cache::forget('home_latest');
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function getMetaTitleAttribute()
    {
        return $this->seo_title ?? ($this->meta['meta_title'] ?? null);
    }

    public function getMetaDescriptionAttribute()
    {
        return $this->seo_description ?? ($this->meta['meta_description'] ?? null);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

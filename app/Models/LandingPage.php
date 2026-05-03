<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'is_active',
        'seo_title',
        'seo_description',
        'hero_headline',
        'hero_subheadline',
        'hero_image',
        'hero_cta_text',
        'problem_points',
        'steps',
        'trust_points',
        'offer_price',
        'original_price',
        'offer_badge',
        'show_cod_badge',
        'show_free_ship',
        'products',
        'reviews',
        'faq',
        'whatsapp_number',
        'trust_description',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'show_cod_badge' => 'boolean',
            'show_free_ship' => 'boolean',
            'offer_price'    => 'decimal:2',
            'original_price' => 'decimal:2',
            'problem_points' => 'array',
            'steps'          => 'array',
            'trust_points'   => 'array',
            'products'       => 'array',
            'reviews'        => 'array',
            'faq'            => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

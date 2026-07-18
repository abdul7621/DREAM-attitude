<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = ['country_code', 'region_state', 'zip_postal_code', 'weight', 'price'];

    protected $casts = [
        'weight' => 'float',
        'price' => 'float',
    ];
}

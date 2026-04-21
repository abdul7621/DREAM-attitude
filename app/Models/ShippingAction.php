<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAction extends Model
{
    protected $fillable = ['rule_id', 'type', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function rule()
    {
        return $this->belongsTo(ShippingRule::class, 'rule_id');
    }
}

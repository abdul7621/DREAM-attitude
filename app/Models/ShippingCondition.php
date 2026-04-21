<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCondition extends Model
{
    protected $fillable = ['rule_id', 'type', 'operator', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function rule()
    {
        return $this->belongsTo(ShippingRule::class, 'rule_id');
    }
}

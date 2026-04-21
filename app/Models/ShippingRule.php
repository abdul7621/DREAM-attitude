<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRule extends Model
{
    protected $fillable = ['name', 'priority', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function conditions()
    {
        return $this->hasMany(ShippingCondition::class, 'rule_id');
    }

    public function action()
    {
        return $this->hasOne(ShippingAction::class, 'rule_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCreditLedger extends Model
{
    protected $table = 'store_credit_ledger';

    protected $fillable = ['user_id', 'amount', 'type', 'reference_type', 'reference_id', 'note'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

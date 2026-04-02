<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $fillable = ['source', 'filename', 'status', 'stats', 'error_log'];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['channel', 'event', 'to_address', 'payload', 'status', 'error'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}

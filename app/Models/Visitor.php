<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'visitor_uuid',
        'user_id',
        'first_source',
        'first_medium',
        'first_campaign',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'region',
        'total_sessions',
        'total_orders',
        'total_revenue',
        'first_seen_at',
        'last_seen_at',
        'normalized_phone',
        'phone_hash',
        'first_capture_source',
        'last_capture_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_capture_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AnalyticsSession::class);
    }
}

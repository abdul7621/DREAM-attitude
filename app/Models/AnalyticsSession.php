<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsSession extends Model
{
    protected $fillable = [
        'session_uuid',
        'visitor_id',
        'source',
        'medium',
        'campaign',
        'landing_page',
        'exit_page',
        'referrer',
        'device_type',
        'page_count',
        'event_count',
        'duration_seconds',
        'is_bounce',
        'reached_product',
        'reached_cart',
        'reached_checkout',
        'reached_purchase',
        'revenue',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_bounce' => 'boolean',
        'reached_product' => 'boolean',
        'reached_cart' => 'boolean',
        'reached_checkout' => 'boolean',
        'reached_purchase' => 'boolean',
        'revenue' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'session_id');
    }
}

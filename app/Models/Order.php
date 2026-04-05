<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // ── Payment methods ──────────────────────────────────────
    public const PAYMENT_COD = 'cod';
    public const PAYMENT_RAZORPAY = 'razorpay';

    // ── Payment statuses ─────────────────────────────────────
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    // ── Order statuses (full lifecycle) ──────────────────────
    public const ORDER_STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const ORDER_STATUS_PLACED = 'placed';
    public const ORDER_STATUS_CONFIRMED = 'confirmed';
    public const ORDER_STATUS_PACKED = 'packed';
    public const ORDER_STATUS_SHIPPED = 'shipped';
    public const ORDER_STATUS_DELIVERED = 'delivered';
    public const ORDER_STATUS_CANCELLED = 'cancelled';
    public const ORDER_STATUS_REFUNDED = 'refunded';

    // ── Allowed forward transitions ──────────────────────────
    public const STATUS_TRANSITIONS = [
        'placed'    => ['confirmed', 'cancelled'],
        'confirmed' => ['packed', 'cancelled'],
        'packed'    => ['shipped', 'cancelled'],
        'shipped'   => ['delivered'],
        'delivered' => ['refunded'],
    ];

    /**
     * Status display labels with colors for the admin UI.
     */
    public const STATUS_LABELS = [
        'awaiting_payment' => ['label' => 'Awaiting Payment', 'color' => 'secondary'],
        'placed'           => ['label' => 'Placed',           'color' => 'info'],
        'confirmed'        => ['label' => 'Confirmed',        'color' => 'primary'],
        'packed'           => ['label' => 'Packed',           'color' => 'warning'],
        'shipped'          => ['label' => 'Shipped',          'color' => 'dark'],
        'delivered'        => ['label' => 'Delivered',        'color' => 'success'],
        'cancelled'        => ['label' => 'Cancelled',        'color' => 'danger'],
        'refunded'         => ['label' => 'Refunded',         'color' => 'danger'],
    ];

    public const PAYMENT_LABELS = [
        'pending'  => ['label' => 'Pending',  'color' => 'warning'],
        'paid'     => ['label' => 'Paid',     'color' => 'success'],
        'failed'   => ['label' => 'Failed',   'color' => 'danger'],
        'refunded' => ['label' => 'Refunded', 'color' => 'secondary'],
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'subtotal',
        'shipping_total',
        'discount_total',
        'tax_total',
        'grand_total',
        'currency',
        'payment_method',
        'payment_status',
        'order_status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'notes',
        'placed_at',
        'coupon_id',
        'coupon_code_snapshot',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'gclid',
        'fbclid',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    // ── Helpers ───────────────────────────────────────────────

    protected static function booted(): void
    {
        static::updated(function (Order $order) {
            $changes = $order->getChanges();
            $watched = ['order_status', 'payment_status', 'grand_total'];
            
            $logChanges = [];
            $logOriginal = [];

            foreach ($watched as $field) {
                if (array_key_exists($field, $changes)) {
                    $logChanges[$field] = $changes[$field];
                    $logOriginal[$field] = $order->getOriginal($field);
                }
            }

            if (!empty($logChanges) && auth()->check()) {
                AuditLog::log('order_updated', $order, $logOriginal, $logChanges);
            }
        });
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::STATUS_TRANSITIONS[$this->order_status] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->order_status]['label'] ?? ucfirst($this->order_status);
    }

    public function statusColor(): string
    {
        return self::STATUS_LABELS[$this->order_status]['color'] ?? 'secondary';
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_status]['label'] ?? ucfirst($this->payment_status);
    }

    public function paymentColor(): string
    {
        return self::PAYMENT_LABELS[$this->payment_status]['color'] ?? 'secondary';
    }

    public function nextStatuses(): array
    {
        return self::STATUS_TRANSITIONS[$this->order_status] ?? [];
    }

    // ── Relationships ────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }
}

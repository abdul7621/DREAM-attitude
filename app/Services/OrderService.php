<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService
    ) {}

    /**
     * @param  array{customer_name: string, email: ?string, phone: string, address_line1: string, address_line2: ?string, city: string, state: string, postal_code: string, country?: string, notes?: ?string}  $data
     */
    public function createCodOrder(Cart $cart, array $data): Order
    {
        $lines = $this->cartService->linesWithPricing();
        if ($lines->isEmpty()) {
            throw new RuntimeException(__('Your cart is empty.'));
        }

        $totals = $this->cartService->computeTotals($data['postal_code']);

        $order = DB::transaction(function () use ($cart, $data, $lines, $totals): Order {
            $this->assertStock($lines);

            $order = Order::query()->create(array_merge([
                'order_number' => $this->newOrderNumber(),
                'user_id' => Auth::id(),
                'customer_name' => $data['customer_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['postal_code'],
                'country' => $data['country'] ?? 'IN',
                'subtotal' => $totals['subtotal'],
                'shipping_total' => $totals['shipping'],
                'discount_total' => $totals['discount'],
                'tax_total' => $totals['tax'],
                'gst_amount' => $totals['tax'],
                'gst_rate' => $totals['gst_rate'] ?? 0,
                'gst_inclusive' => $totals['gst_inclusive'] ?? true,
                'grand_total' => $totals['grand'],
                'currency' => config('commerce.currency', 'INR'),
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'order_status' => Order::ORDER_STATUS_PLACED,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
                'coupon_id' => $totals['coupon']?->id,
                'coupon_code_snapshot' => $totals['coupon']?->code,
            ], $this->attributionAttributes()));

            foreach ($lines as $row) {
                /** @var \App\Models\ProductVariant $variant */
                $variant = $row['variant'];
                $product = $variant->product;
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $product->name,
                    'variant_title_snapshot' => $variant->title,
                    'sku_snapshot' => $variant->sku,
                    'qty' => $row['item']->qty,
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                ]);
                // COD: stock deducts immediately (order is confirmed on creation)
                $this->decrementStock($variant, $row['item']->qty);

                Log::info('[STOCK_DEBUG] COD stock deducted', [
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'qty_deducted' => $row['item']->qty,
                    'stock_after' => $variant->fresh()->stock_qty ?? 'N/A',
                ]);
            }

            if ($order->coupon_id) {
                $c = Coupon::query()->find($order->coupon_id);
                if ($c) {
                    $this->couponService->incrementUsage($c);
                }
            }

            $this->createPendingShipment($order);

            $this->cartService->clear($cart);

            return $order;
        });

        Log::info('[STOCK_DEBUG] COD order created', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
        ]);

        OrderPlaced::dispatch($order);

        if (app(\App\Services\SettingsService::class)->get('shipping.auto_create', true)) {
            \App\Jobs\CreateShipmentJob::dispatch($order);
        }

        return $order;
    }

    /**
     * Create a pending online order WITHOUT deducting stock.
     * Stock is deducted only when payment is confirmed (in finalizeOnlinePayment).
     *
     * @param  array{customer_name: string, email: ?string, phone: string, address_line1: string, address_line2: ?string, city: string, state: string, postal_code: string, country?: string, notes?: ?string}  $data
     */
    public function createPendingOnlineOrder(Cart $cart, array $data): Order
    {
        $lines = $this->cartService->linesWithPricing();
        if ($lines->isEmpty()) {
            throw new RuntimeException(__('Your cart is empty.'));
        }

        $totals = $this->cartService->computeTotals($data['postal_code']);

        // Fix #4: Check for reusable order (payment retry)
        $reusable = $this->findReusableOrder($lines);
        if ($reusable) {
            Log::info('[STOCK_DEBUG] Reusing existing order for retry', [
                'order_id' => $reusable->id,
                'order_number' => $reusable->order_number,
                'original_status' => $reusable->order_status,
            ]);

            // Update order details in case address/totals changed
            $reusable->update([
                'customer_name' => $data['customer_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['postal_code'],
                'country' => $data['country'] ?? 'IN',
                'subtotal' => $totals['subtotal'],
                'shipping_total' => $totals['shipping'],
                'discount_total' => $totals['discount'],
                'tax_total' => $totals['tax'],
                'grand_total' => $totals['grand'],
                'payment_method' => $data['payment_method'],
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'order_status' => Order::ORDER_STATUS_AWAITING_PAYMENT,
                'notes' => $data['notes'] ?? null,
            ]);

            // Reset gateway_order_id to force new payment session creation
            $reusable->gateway_order_id = null;
            $reusable->save();

            return $reusable;
        }

        return DB::transaction(function () use ($data, $lines, $totals): Order {
            $this->assertStock($lines);

            $order = Order::query()->create(array_merge([
                'order_number' => $this->newOrderNumber(),
                'user_id' => Auth::id(),
                'customer_name' => $data['customer_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['postal_code'],
                'country' => $data['country'] ?? 'IN',
                'subtotal' => $totals['subtotal'],
                'shipping_total' => $totals['shipping'],
                'discount_total' => $totals['discount'],
                'tax_total' => $totals['tax'],
                'gst_amount' => $totals['tax'],
                'gst_rate' => $totals['gst_rate'] ?? 0,
                'gst_inclusive' => $totals['gst_inclusive'] ?? true,
                'grand_total' => $totals['grand'],
                'currency' => config('commerce.currency', 'INR'),
                'payment_method' => $data['payment_method'],
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'order_status' => Order::ORDER_STATUS_AWAITING_PAYMENT,
                'notes' => $data['notes'] ?? null,
                'placed_at' => null,
                'coupon_id' => $totals['coupon']?->id,
                'coupon_code_snapshot' => $totals['coupon']?->code,
            ], $this->attributionAttributes()));

            foreach ($lines as $row) {
                /** @var \App\Models\ProductVariant $variant */
                $variant = $row['variant'];
                $product = $variant->product;
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $product->name,
                    'variant_title_snapshot' => $variant->title,
                    'sku_snapshot' => $variant->sku,
                    'qty' => $row['item']->qty,
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                ]);
                // Fix #1: NO stock deduction here for online orders.
                // Stock deducts only on payment confirmation in finalizeOnlinePayment().
            }

            $this->createPendingShipment($order);

            Log::info('[STOCK_DEBUG] Online order created (no stock deducted)', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
            ]);

            return $order;
        });
    }

    /**
     * Finalize an online payment with idempotency protection.
     * Stock is deducted HERE (only for online orders), inside a DB transaction with row-level lock.
     */
    public function finalizeOnlinePayment(Order $order, array $requestData): void
    {
        // Fix #3: Idempotency check INSIDE transaction with lockForUpdate
        $alreadyPaid = DB::transaction(function () use ($order): bool {
            // Lock the order row to prevent concurrent finalization
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status === Order::PAYMENT_STATUS_PAID) {
                Log::info('[STOCK_DEBUG] finalizeOnlinePayment skipped (already PAID)', [
                    'order_id' => $locked->id,
                ]);
                return true; // Already processed
            }

            // Fix #1 continued: Deduct stock NOW (on payment success)
            $order->load('orderItems.variant');
            foreach ($order->orderItems as $item) {
                if ($item->variant && $item->variant->track_inventory) {
                    $fresh = ProductVariant::query()
                        ->whereKey($item->variant->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($fresh->stock_qty < $item->qty) {
                        // Stock gone during payment window — cancel order with clean message
                        $locked->update([
                            'payment_status' => Order::PAYMENT_STATUS_FAILED,
                            'order_status' => Order::ORDER_STATUS_CANCELLED,
                            'notes' => 'Stock unavailable after payment: ' . $item->product_name_snapshot,
                        ]);

                        Log::warning('[STOCK_DEBUG] Stock insufficient at finalize, order cancelled', [
                            'order_id' => $order->id,
                            'variant_id' => $item->variant->id,
                            'required' => $item->qty,
                            'available' => $fresh->stock_qty,
                        ]);

                        throw new RuntimeException(
                            __('Sorry, :name is no longer available in the requested quantity. Your payment will be refunded.', [
                                'name' => $item->product_name_snapshot,
                            ])
                        );
                    }

                    $fresh->decrement('stock_qty', $item->qty);

                    Log::info('[STOCK_DEBUG] Online stock deducted at finalize', [
                        'order_id' => $order->id,
                        'variant_id' => $item->variant->id,
                        'qty_deducted' => $item->qty,
                        'stock_after' => $fresh->stock_qty - $item->qty,
                    ]);
                }
            }

            // Mark as paid
            $locked->update([
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'order_status' => Order::ORDER_STATUS_PLACED,
                'placed_at' => now(),
            ]);

            if ($locked->coupon_id) {
                $c = Coupon::query()->find($locked->coupon_id);
                if ($c) {
                    $this->couponService->incrementUsage($c);
                }
            }

            $cart = $this->cartService->getCart();
            $this->cartService->clear($cart);

            Log::info('[STOCK_DEBUG] Online payment finalized', [
                'order_id' => $locked->id,
                'order_number' => $locked->order_number,
                'payment_status' => 'paid',
            ]);

            return false;
        });

        if ($alreadyPaid) {
            return; // Skip event dispatch — already done on first finalization
        }

        // Fire events AFTER transaction commits (outside transaction)
        OrderPlaced::dispatch($order->fresh());

        if (app(\App\Services\SettingsService::class)->get('shipping.auto_create', true)) {
            \App\Jobs\CreateShipmentJob::dispatch($order->fresh());
        }
    }

    /**
     * Fix #2: Restore stock for an order (safety net for failed payments).
     * Only restores if the order has items with inventory-tracked variants.
     */
    public function restoreStock(Order $order): void
    {
        $order->load('orderItems.variant');

        foreach ($order->orderItems as $item) {
            if ($item->variant && $item->variant->track_inventory) {
                // Only restore stock if the payment was actually finalized (stock was deducted)
                // For online orders: stock is only deducted in finalizeOnlinePayment
                // So we check if payment_status was PAID before failure
                $item->variant->increment('stock_qty', $item->qty);

                Log::info('[STOCK_DEBUG] Stock restored', [
                    'order_id' => $order->id,
                    'variant_id' => $item->variant->id,
                    'qty_restored' => $item->qty,
                    'stock_after' => $item->variant->fresh()->stock_qty,
                ]);
            }
        }
    }

    /**
     * Fix #4: Find a reusable AWAITING_PAYMENT order created within last 15 minutes
     * with matching cart items (same variants and quantities).
     */
    private function findReusableOrder($lines): ?Order
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }

        // Find recent AWAITING_PAYMENT or ABANDONED orders for this user
        $candidates = Order::query()
            ->where('user_id', $userId)
            ->whereIn('order_status', [
                Order::ORDER_STATUS_AWAITING_PAYMENT,
                Order::ORDER_STATUS_ABANDONED,
            ])
            ->whereIn('payment_status', [
                Order::PAYMENT_STATUS_PENDING,
                Order::PAYMENT_STATUS_FAILED,
            ])
            ->where('created_at', '>=', now()->subMinutes(15))
            ->with('orderItems')
            ->orderByDesc('created_at')
            ->get();

        foreach ($candidates as $order) {
            // Check if cart items match the order items
            $orderVariants = $order->orderItems
                ->pluck('qty', 'product_variant_id')
                ->toArray();

            $cartVariants = [];
            foreach ($lines as $row) {
                $cartVariants[$row['variant']->id] = $row['item']->qty;
            }

            // Must have same variants with same quantities
            if ($orderVariants == $cartVariants) {
                return $order;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function attributionAttributes(): array
    {
        return [
            'utm_source' => session('attr_utm_source'),
            'utm_medium' => session('attr_utm_medium'),
            'utm_campaign' => session('attr_utm_campaign'),
            'utm_content' => session('attr_utm_content'),
            'utm_term' => session('attr_utm_term'),
            'gclid' => session('attr_gclid'),
            'fbclid' => session('attr_fbclid'),
        ];
    }

    private function createPendingShipment(Order $order): void
    {
        Shipment::query()->create([
            'order_id' => $order->id,
            'carrier' => 'manual',
            'status' => 'pending',
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{item: \App\Models\CartItem, variant: \App\Models\ProductVariant, product: \App\Models\Product, unit_price: string, line_total: string}>  $lines
     */
    private function assertStock($lines): void
    {
        foreach ($lines as $row) {
            /** @var ProductVariant $variant */
            $variant = $row['variant'];
            $qty = $row['item']->qty;
            if ($variant->track_inventory && $variant->stock_qty < $qty) {
                throw new RuntimeException(__('Not enough stock for :name.', ['name' => $variant->product->name]));
            }
        }
    }

    private function decrementStock(ProductVariant $variant, int $qty): void
    {
        if (! $variant->track_inventory) {
            return;
        }

        $fresh = ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();
        if ($fresh->stock_qty < $qty) {
            throw new RuntimeException(__('Not enough stock.'));
        }
        $fresh->decrement('stock_qty', $qty);
    }

    private function newOrderNumber(): string
    {
        do {
            $n = 'ORD-'.now()->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        } while (Order::query()->where('order_number', $n)->exists());

        return $n;
    }
}

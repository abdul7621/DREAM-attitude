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

        $this->assertStock($lines);

        $totals = $this->cartService->computeTotals($data['postal_code']);

        return DB::transaction(function () use ($cart, $data, $lines, $totals): Order {
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
                $this->decrementStock($variant, $row['item']->qty);
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
    }

    /**
     * @param  array{customer_name: string, email: ?string, phone: string, address_line1: string, address_line2: ?string, city: string, state: string, postal_code: string, country?: string, notes?: ?string}  $data
     */
    public function createPendingOnlineOrder(Cart $cart, array $data): Order
    {
        $lines = $this->cartService->linesWithPricing();
        if ($lines->isEmpty()) {
            throw new RuntimeException(__('Your cart is empty.'));
        }

        $this->assertStock($lines);

        $totals = $this->cartService->computeTotals($data['postal_code']);

        return DB::transaction(function () use ($data, $lines, $totals): Order {
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
                // Reserve stock upfront for online payment!
                $this->decrementStock($variant, $row['item']->qty);
            }

            $this->createPendingShipment($order);

            return $order;
        });
    }

    public function finalizeOnlinePayment(Order $order, array $requestData): void
    {
        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return;
        }

        DB::transaction(function () use ($order, $requestData): void {
            $order->update([
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'order_status' => Order::ORDER_STATUS_PLACED,
                'placed_at' => now(),
            ]);

            if ($order->coupon_id) {
                $c = Coupon::query()->find($order->coupon_id);
                if ($c) {
                    $this->couponService->incrementUsage($c);
                }
            }

            $cart = $this->cartService->getCart();
            $this->cartService->clear($cart);
        });

        // Fire after transaction commits
        OrderPlaced::dispatch($order);
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

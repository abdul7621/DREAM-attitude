<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CartService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly CouponService $coupons,
        private readonly ShippingService $shipping
    ) {}

    public function getCart(): Cart
    {
        if (Auth::check()) {
            return $this->touchActivity(Cart::query()->firstOrCreate(
                ['user_id' => Auth::id()],
                ['currency' => config('commerce.currency', 'INR')]
            ));
        }

        $cid = session('cart_id');
        if ($cid) {
            $c = Cart::query()->whereNull('user_id')->find($cid);
            if ($c) {
                return $this->touchActivity($c);
            }
        }

        $c = Cart::query()->create([
            'user_id' => null,
            'currency' => config('commerce.currency', 'INR'),
        ]);
        session(['cart_id' => $c->id]);

        return $this->touchActivity($c);
    }

    private function touchActivity(Cart $cart): Cart
    {
        if (Schema::hasColumn('carts', 'last_activity_at')) {
            $cart->forceFill(['last_activity_at' => now()])->saveQuietly();
        }

        return $cart;
    }

    public function count(): int
    {
        $cart = $this->getCart();

        return (int) $cart->items()->sum('qty');
    }

    /**
     * @return Collection<int, array{item: CartItem, variant: ProductVariant, unit_price: string, line_total: string}>
     */
    public function linesWithPricing(): Collection
    {
        $cart = $this->getCart();
        $user = Auth::user();

        return $cart->items()->with(['variant.product'])->get()->map(function (CartItem $item) use ($user) {
            $variant = $item->variant;
            if (! $variant || ! $variant->is_active) {
                return null;
            }
            $product = $variant->product;
            if (! $product || $product->status !== \App\Models\Product::STATUS_ACTIVE) {
                return null;
            }

            $unit = $this->pricing->unitPriceForCustomer($variant, $user instanceof User ? $user : null, max(1, $item->qty));
            $line = $this->moneyMul($unit, $item->qty);

            return [
                'item' => $item,
                'variant' => $variant,
                'product' => $product,
                'unit_price' => $unit,
                'line_total' => $line,
            ];
        })->filter()->values();
    }

    public function subtotalFormatted(): string
    {
        $sum = '0.00';
        foreach ($this->linesWithPricing() as $row) {
            $sum = $this->moneyAdd($sum, $row['line_total']);
        }

        return $sum;
    }

    public function add(int $variantId, int $qty): void
    {
        if ($qty < 1) {
            return;
        }

        $variant = ProductVariant::query()->with('product')->findOrFail($variantId);
        if (! $variant->is_active || $variant->product->status !== \App\Models\Product::STATUS_ACTIVE) {
            abort(422, __('This product is not available.'));
        }

        if ($this->maxQty($variant, $qty) < 1) {
            abort(422, __('This product is out of stock.'));
        }

        $cart = $this->getCart();

        DB::transaction(function () use ($cart, $variant, $qty): void {
            $existing = $cart->items()->where('product_variant_id', $variant->id)->lockForUpdate()->first();
            if ($existing) {
                $combined = $existing->qty + $qty;
                $newQty = $this->maxQty($variant, $combined);
                $existing->update(['qty' => $newQty]);
            } else {
                $cart->items()->create([
                    'product_variant_id' => $variant->id,
                    'qty' => $this->maxQty($variant, $qty),
                ]);
            }
        });
    }

    public function updateQty(CartItem $cartItem, int $qty): void
    {
        $cart = $this->getCart();
        abort_unless($cartItem->cart_id === $cart->id, 403);

        if ($qty < 1) {
            $cartItem->delete();

            return;
        }

        $variant = $cartItem->variant()->with('product')->firstOrFail();
        $max = $this->maxQty($variant, $qty);
        if ($max < 1) {
            $cartItem->delete();

            return;
        }

        $cartItem->update(['qty' => min($qty, $max)]);
    }

    public function remove(CartItem $cartItem): void
    {
        $cart = $this->getCart();
        abort_unless($cartItem->cart_id === $cart->id, 403);
        $cartItem->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        session()->forget('cart_coupon_id');
    }

    public function applyCouponCode(string $code): Coupon
    {
        $sub = $this->subtotalFormatted();
        $coupon = $this->coupons->findValidByCode($code);
        if (! $coupon) {
            throw new RuntimeException(__('Invalid or expired coupon.'));
        }
        $this->coupons->validateForCart($coupon, $sub);
        session(['cart_coupon_id' => $coupon->id]);

        return $coupon;
    }

    public function removeCoupon(): void
    {
        session()->forget('cart_coupon_id');
    }

    public function getAppliedCoupon(): ?Coupon
    {
        $id = session('cart_coupon_id');
        if (! $id) {
            return null;
        }

        return Coupon::query()->where('is_active', true)->find($id);
    }

    public function discountAmount(): string
    {
        $c = $this->getAppliedCoupon();
        if (! $c) {
            return '0.00';
        }

        return $this->coupons->discountAmount($c, $this->subtotalFormatted());
    }

    public function totalWeightGrams(): int
    {
        $sum = 0;
        foreach ($this->linesWithPricing() as $row) {
            $w = (int) ($row['variant']->weight_grams ?? 0);
            $sum += $w * (int) $row['item']->qty;
        }

        return max(0, $sum);
    }

    /**
     * @return array{subtotal: string, discount: string, shipping: string, tax: string, grand: string, coupon: ?Coupon}
     */
    public function computeTotals(string $postalCode = ''): array
    {
        $subtotal = $this->subtotalFormatted();
        $coupon = $this->getAppliedCoupon();
        if ($coupon) {
            if (! $coupon->isValidNow()) {
                session()->forget('cart_coupon_id');
                $coupon = null;
            } else {
                try {
                    $this->coupons->validateForCart($coupon, $subtotal);
                } catch (RuntimeException) {
                    session()->forget('cart_coupon_id');
                    $coupon = null;
                }
            }
        }
        $discount = $coupon ? $this->coupons->discountAmount($coupon, $subtotal) : '0.00';
        $pc = trim($postalCode);
        $shipping = $pc !== ''
            ? $this->shipping->quote($pc, $this->totalWeightGrams(), $subtotal)
            : '0.00';
        
        $afterDisc = $this->subMoney($subtotal, $discount);
        
        $tax = '0.00';
        $settings = app(\App\Services\SettingsService::class);
        $taxInclusive = true;
        if ($settings->get('gst.enabled')) {
            $rate = (float) $settings->get('gst.rate', 18);
            $inclusive = (bool) $settings->get('gst.inclusive', true);
            $taxInclusive = $inclusive;
            
            $taxableAmount = (float) $afterDisc;
            if ($inclusive) {
                $taxVal = ($taxableAmount * $rate) / (100 + $rate);
                $tax = number_format($taxVal, 2, '.', '');
            } else {
                $taxVal = ($taxableAmount * $rate) / 100;
                $tax = number_format($taxVal, 2, '.', '');
            }
        }

        $grandBeforeTax = $this->moneyAdd($afterDisc, $shipping);
        $grand = $taxInclusive ? $grandBeforeTax : $this->moneyAdd($grandBeforeTax, $tax);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'gst_rate' => $settings->get('gst.enabled') ? $settings->get('gst.rate', 18) : 0,
            'gst_inclusive' => $settings->get('gst.enabled') ? $settings->get('gst.inclusive', true) : true,
            'grand' => $grand,
            'coupon' => $coupon,
        ];
    }

    private function subMoney(string $a, string $b): string
    {
        return number_format(max(0, (float) $a - (float) $b), 2, '.', '');
    }

    public function mergeOnLogin(User $user): void
    {
        $guestId = session('cart_id');
        $guest = $guestId ? Cart::query()->whereNull('user_id')->with('items')->find($guestId) : null;

        $userCart = Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['currency' => config('commerce.currency', 'INR')]
        );

        if ($guest && $guest->id !== $userCart->id) {
            DB::transaction(function () use ($guest, $userCart): void {
                foreach ($guest->items as $gi) {
                    $variant = ProductVariant::query()->with('product')->find($gi->product_variant_id);
                    if (! $variant || ! $variant->is_active || $variant->product->status !== \App\Models\Product::STATUS_ACTIVE) {
                        continue;
                    }

                    $existing = $userCart->items()->where('product_variant_id', $variant->id)->lockForUpdate()->first();
                    $combined = $existing ? $existing->qty + $gi->qty : $gi->qty;
                    $finalQty = $this->maxQty($variant, $combined);
                    if ($finalQty < 1) {
                        continue;
                    }
                    if ($existing) {
                        $existing->update(['qty' => $finalQty]);
                    } else {
                        $userCart->items()->create([
                            'product_variant_id' => $variant->id,
                            'qty' => $finalQty,
                        ]);
                    }
                }
                $guest->delete();
            });
        }

        session(['cart_id' => $userCart->id]);
    }

    private function maxQty(ProductVariant $variant, int $desired): int
    {
        if (! $variant->track_inventory) {
            return max(1, $desired);
        }

        return min($variant->stock_qty, max(1, $desired));
    }

    private function moneyAdd(string $a, string $b): string
    {
        return number_format((float) $a + (float) $b, 2, '.', '');
    }

    private function moneyMul(string $unit, int $qty): string
    {
        return number_format(round((float) $unit * $qty, 2), 2, '.', '');
    }
}

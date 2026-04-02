<?php

namespace App\Services;

use App\Models\ShippingRule;

class ShippingService
{
    public function quote(string $postalCode, int $weightGramsTotal, string $subtotal): string
    {
        $rules = ShippingRule::query()->where('is_active', true)->orderByDesc('priority')->get();
        foreach ($rules as $rule) {
            $amt = $this->matchRule($rule, $postalCode, $weightGramsTotal, $subtotal);
            if ($amt !== null) {
                return number_format($amt, 2, '.', '');
            }
        }

        return '0.00';
    }

    private function matchRule(ShippingRule $rule, string $postalCode, int $weightGrams, string $subtotal): ?float
    {
        $cfg = $rule->config ?? [];

        return match ($rule->type) {
            'flat' => isset($cfg['amount']) ? (float) $cfg['amount'] : null,
            'weight' => $this->weightBands($cfg['bands'] ?? [], $weightGrams),
            'pincode' => $this->pincodePrefixes($cfg, $postalCode),
            default => null,
        };
    }

    /**
     * @param  array<int, array{max_g: int|float, price: float}>  $bands
     */
    private function weightBands(array $bands, int $weightGrams): ?float
    {
        foreach ($bands as $b) {
            $max = (int) ($b['max_g'] ?? 0);
            $price = (float) ($b['price'] ?? 0);
            if ($weightGrams <= $max) {
                return $price;
            }
        }

        return null;
    }

    /**
     * @param  array{prefixes?: array<string, float>, default?: float}  $cfg
     */
    private function pincodePrefixes(array $cfg, string $postalCode): ?float
    {
        $pc = preg_replace('/\D/', '', $postalCode) ?? '';
        if ($pc === '') {
            return isset($cfg['default']) ? (float) $cfg['default'] : null;
        }
        $prefix2 = substr($pc, 0, 2);
        $prefixes = $cfg['prefixes'] ?? [];
        if (isset($prefixes[$prefix2])) {
            return (float) $prefixes[$prefix2];
        }

        return isset($cfg['default']) ? (float) $cfg['default'] : null;
    }
}

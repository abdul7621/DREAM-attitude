<?php

namespace App\Services;

use App\Models\ShippingRule;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected PincodeService $pincodeService;

    public function __construct(PincodeService $pincodeService)
    {
        $this->pincodeService = $pincodeService;
    }

    public function quote(string $postalCode, ?string $paymentMethod, int $weightGramsTotal, string $subtotal): string
    {
        $rules = ShippingRule::with(['conditions', 'action'])
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get();
        
        $location = $this->pincodeService->resolve($postalCode);
        
        $context = [
            'order_value'    => (float) $subtotal,
            'state'          => $location ? $location['state'] : null,
            'city'           => $location ? $location['city'] : null,
            'pincode_prefix' => substr(preg_replace('/\D/', '', $postalCode), 0, 3) ?: null,
            'payment_method' => $paymentMethod ? strtoupper($paymentMethod) : null,
            'weight'         => $weightGramsTotal
        ];

        Log::info("Evaluating Advanced Shipping Rules", ['context' => $context]);

        foreach ($rules as $rule) {
            if ($this->matchRule($rule, $context)) {
                $amt = $this->applyAction($rule, $context);
                
                if ($amt !== null) {
                    Log::info("Shipping Rule Matched: [ID: {$rule->id}] {$rule->name} (Priority: {$rule->priority}). Applied Amount: {$amt}");
                    return number_format($amt, 2, '.', '');
                }
            }
        }

        Log::info("No Shipping Rule matched. Defaulting to 0.00");
        return '0.00';
    }

    private function matchRule(ShippingRule $rule, array $context): bool
    {
        if ($rule->conditions->isEmpty()) {
            return true; // No conditions = ALWAYS appplies (e.g. Default fallback)
        }

        foreach ($rule->conditions as $cond) {
            $contextValue = $context[$cond->type] ?? null;
            $ruleValue = $cond->value; // This is automatically cast to array because of Model casts

            $matched = match ($cond->operator) {
                '=='      => $contextValue == ($ruleValue[0] ?? null),
                '!='      => $contextValue != ($ruleValue[0] ?? null),
                '>'       => $contextValue > ($ruleValue[0] ?? 0),
                '<'       => $contextValue < ($ruleValue[0] ?? 0),
                '>='      => $contextValue >= ($ruleValue[0] ?? 0),
                '<='      => $contextValue <= ($ruleValue[0] ?? 0),
                'in'      => is_array($ruleValue) && in_array($contextValue, $ruleValue),
                'not_in'  => is_array($ruleValue) && !in_array($contextValue, $ruleValue),
                default   => false,
            };

            if (!$matched) {
                return false; // ALL conditions must match
            }
        }

        return true;
    }

    private function applyAction(ShippingRule $rule, array $context): ?float
    {
        if (!$rule->action) {
            return null;
        }

        return match ($rule->action->type) {
            'flat'       => (float) $rule->action->value,
            'free'       => 0.00,
            'percentage' => (float) ($context['order_value'] * ($rule->action->value / 100)),
            'per_kg'     => (float) (ceil($context['weight'] / 1000) * $rule->action->value),
            default      => null,
        };
    }
}

<?php

namespace App\Services;

use App\Models\ShippingRule;
use App\Models\ShippingRate;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected PincodeService $pincodeService;

    public function __construct(PincodeService $pincodeService)
    {
        $this->pincodeService = $pincodeService;
    }

    public function quote(string $postalCode, ?string $paymentMethod, int $weightGramsTotal, string $subtotal, ?string $countryCode = 'IN'): string
    {
        $countryCode = $countryCode ? strtoupper(trim($countryCode)) : 'IN';
        $weightKg = $weightGramsTotal / 1000.0;

        // Resolve 3-letter ISO code for the country
        $iso3Country = $this->getIso3CountryCode($countryCode);

        if ($iso3Country) {
            // Find weight-based rates for this country
            $rates = ShippingRate::where('country_code', $iso3Country)
                ->orderBy('weight', 'asc')
                ->get();

            if ($rates->isNotEmpty()) {
                $selectedPrice = null;
                foreach ($rates as $rate) {
                    if ($weightKg >= $rate->weight) {
                        $selectedPrice = $rate->price;
                    }
                }

                if ($selectedPrice === null) {
                    // Weight is below the minimum threshold in table, use the lowest rate
                    $selectedPrice = $rates->first()->price;
                }

                Log::info("Shipping Quote from Database ShippingRate for Country: {$iso3Country} ({$countryCode}), Weight: {$weightKg}kg -> Price: {$selectedPrice}");
                return number_format($selectedPrice, 2, '.', '');
            }
        }

        // Fallback to advanced rules (existing domestic rules)
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
            'weight'         => $weightGramsTotal,
            'country'        => $countryCode
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

        // Final fallback: if country is not IN, default to setting or flat 2500.00
        if ($countryCode !== 'IN') {
            $settings = app(\App\Services\SettingsService::class);
            $flatRate = (float) $settings->get('shipping.international_flat_rate', 2500.00);
            Log::info("No specific rates or rules matched for international country {$countryCode}. Using default flat rate: {$flatRate}");
            return number_format($flatRate, 2, '.', '');
        }

        Log::info("No Shipping Rule matched. Defaulting to 0.00");
        return '0.00';
    }

    private function matchRule(ShippingRule $rule, array $context): bool
    {
        if ($rule->conditions->isEmpty()) {
            return true; // No conditions = ALWAYS applies (e.g. Default fallback)
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

    private function getIso3CountryCode(string $countryCode): ?string
    {
        $countryCode = strtoupper(trim($countryCode));
        if (strlen($countryCode) === 3) {
            return $countryCode;
        }

        $map = [
            'AF' => 'AFG', 'AO' => 'AGO', 'AL' => 'ALB', 'AD' => 'AND', 'AE' => 'ARE',
            'AR' => 'ARG', 'AM' => 'ARM', 'AT' => 'AUT', 'AZ' => 'AZE', 'BI' => 'BDI',
            'BE' => 'BEL', 'BJ' => 'BEN', 'BF' => 'BFA', 'BD' => 'BGD', 'BG' => 'BGR',
            'BH' => 'BHR', 'BS' => 'BHS', 'BA' => 'BIH', 'BY' => 'BLR', 'BZ' => 'BLZ',
            'BO' => 'BOL', 'BR' => 'BRA', 'BB' => 'BRB', 'BN' => 'BRN', 'BT' => 'BTN',
            'BW' => 'BWA', 'CF' => 'CAF', 'CA' => 'CAN', 'CH' => 'CHE', 'CL' => 'CHL',
            'CN' => 'CHN', 'CM' => 'CMR', 'CD' => 'COD', 'CG' => 'COG', 'CO' => 'COL',
            'KM' => 'COM', 'CV' => 'CPV', 'CR' => 'CRI', 'CU' => 'CUB', 'CY' => 'CYP',
            'CZ' => 'CZE', 'DE' => 'DEU', 'DJ' => 'DJI', 'DM' => 'DMA', 'DK' => 'DNK',
            'DO' => 'DOM', 'DZ' => 'DZA', 'EC' => 'ECU', 'EG' => 'EGY', 'ER' => 'ERI',
            'ES' => 'ESP', 'EE' => 'EST', 'ET' => 'ETH', 'FI' => 'FIN', 'FJ' => 'FJI',
            'FR' => 'FRA', 'GA' => 'GAB', 'GB' => 'GBR', 'GE' => 'GEO', 'GH' => 'GHA',
            'GN' => 'GIN', 'GM' => 'GMB', 'GQ' => 'GNQ', 'GR' => 'GRC', 'GT' => 'GTM',
            'GY' => 'GUY', 'HN' => 'HND', 'HR' => 'HRV', 'HT' => 'HTI', 'HU' => 'HUN',
            'ID' => 'IDN', 'IN' => 'IND', 'IE' => 'IRL', 'IR' => 'IRN', 'IQ' => 'IRQ',
            'IS' => 'ISL', 'IL' => 'ISR', 'IT' => 'ITA', 'JM' => 'JAM', 'JO' => 'JOR',
            'JP' => 'JPN', 'KZ' => 'KAZ', 'KE' => 'KEN', 'KG' => 'KGZ', 'KH' => 'KHM',
            'KR' => 'KOR', 'KW' => 'KWT', 'LA' => 'LAO', 'LB' => 'LBN', 'LY' => 'LBY',
            'LK' => 'LKA', 'LT' => 'LTU', 'LU' => 'LUX', 'LV' => 'LVA', 'MA' => 'MAR',
            'MD' => 'MDA', 'MG' => 'MDG', 'MV' => 'MDV', 'MX' => 'MEX', 'ML' => 'MLI',
            'MT' => 'MLT', 'MM' => 'MMR', 'MN' => 'MNG', 'MZ' => 'MOZ', 'MW' => 'MWI',
            'MY' => 'MYS', 'NA' => 'NAM', 'NG' => 'NGA', 'NI' => 'NIC', 'NL' => 'NLD',
            'NO' => 'NOR', 'NP' => 'NPL', 'NZ' => 'NZL', 'OM' => 'OMN', 'PK' => 'PAK',
            'PA' => 'PAN', 'PE' => 'PER', 'PH' => 'PHL', 'PL' => 'POL', 'KP' => 'PRK',
            'PT' => 'PRT', 'PY' => 'PRY', 'QA' => 'QAT', 'RO' => 'ROU', 'RU' => 'RUS',
            'SA' => 'SAU', 'SD' => 'SDN', 'SE' => 'SWE', 'SG' => 'SGP', 'SI' => 'SVN',
            'SK' => 'SVK', 'SN' => 'SEN', 'SO' => 'SOM', 'SR' => 'SUR', 'SS' => 'SSD',
            'SV' => 'SLV', 'SY' => 'SYR', 'SZ' => 'SWZ', 'TD' => 'TCD', 'TG' => 'TGO',
            'TH' => 'THA', 'TJ' => 'TJK', 'TL' => 'TLS', 'TM' => 'TKM', 'TN' => 'TUN',
            'TR' => 'TUR', 'TT' => 'TTO', 'TW' => 'TWN', 'TZ' => 'TZA', 'UA' => 'UKR',
            'UG' => 'UGA', 'US' => 'USA', 'UY' => 'URY', 'UZ' => 'UZB', 'VC' => 'VCT',
            'VE' => 'VEN', 'VN' => 'VNM', 'YE' => 'YEM', 'ZA' => 'ZAF', 'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        ];

        return $map[$countryCode] ?? null;
    }
}

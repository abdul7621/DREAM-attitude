<?php

namespace App\Services;

use App\Models\PincodeCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PincodeService
{
    /**
     * Resolve pincode to State and City using local DB cache or India Post API.
     * 
     * @param string $pincode
     * @return array{state: string, city: string}|null
     */
    public function resolve(string $pincode): ?array
    {
        $pc = preg_replace('/\D/', '', $pincode);
        if (strlen($pc) !== 6) {
            return null;
        }

        // 1. Check database cache
        $cached = PincodeCache::where('postal_code', $pc)->first();
        if ($cached) {
            return [
                'state' => $cached->state,
                'city'  => $cached->city,
            ];
        }

        // 2. Fetch from India Post API
        try {
            $response = Http::timeout(3)->get('https://api.postalpincode.in/pincode/' . $pc);
            
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data[0]['Status']) && $data[0]['Status'] === 'Success' && !empty($data[0]['PostOffice'])) {
                    $postOffice = $data[0]['PostOffice'][0];
                    $state = $postOffice['State'] ?? '';
                    $city = $postOffice['District'] ?? '';

                    if ($state) {
                        try {
                            PincodeCache::create([
                                'postal_code' => $pc,
                                'state'       => $state,
                                'city'        => $city
                            ]);
                        } catch (\Exception $e) {
                            // Ignore unique constraint races if multiple requests inserted at exactly the same ms
                        }
                        
                        return [
                            'state' => $state,
                            'city'  => $city,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("PincodeService API lookup failed for {$pc}: " . $e->getMessage());
        }

        return null;
    }
}

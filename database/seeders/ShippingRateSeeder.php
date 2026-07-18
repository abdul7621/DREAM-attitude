<?php

namespace Database\Seeders;

use App\Models\ShippingRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('tablerates.csv');
        if (!file_exists($path)) {
            $fallback = 'C:/Users/ADMIN/ruby/tablerates.csv';
            if (file_exists($fallback)) {
                copy($fallback, $path);
            }
        }

        if (!file_exists($path)) {
            Log::warning("Seeder failed: tablerates.csv not found.");
            return;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return;
        }

        // Truncate existing records
        ShippingRate::truncate();

        // Read header
        fgetcsv($handle);

        $records = [];
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) {
                continue;
            }

            $records[] = [
                'country_code'    => strtoupper(trim($row[0])),
                'region_state'    => trim($row[1]),
                'zip_postal_code' => trim($row[2]),
                'weight'          => (float) $row[3],
                'price'           => (float) $row[4],
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            if (count($records) >= 200) {
                ShippingRate::insert($records);
                $records = [];
            }
        }

        if (count($records) > 0) {
            ShippingRate::insert($records);
        }

        fclose($handle);
    }
}

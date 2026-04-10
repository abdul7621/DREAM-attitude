<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $methods = [
            [
                'name' => 'razorpay',
                'label' => 'Razorpay',
                'driver' => 'razorpay',
                'is_active' => true,
                'is_default' => true,
                'config' => json_encode(['key_id' => '', 'key_secret' => '']),
                'sort_order' => 1,
            ],
            [
                'name' => 'phonepe',
                'label' => 'PhonePe',
                'driver' => 'phonepe',
                'is_active' => false,
                'is_default' => false,
                'config' => json_encode(['merchant_id' => '', 'salt_key' => '', 'salt_index' => '1', 'env' => 'UAT']),
                'sort_order' => 2,
            ],
            [
                'name' => 'cashfree',
                'label' => 'Cashfree',
                'driver' => 'cashfree',
                'is_active' => false,
                'is_default' => false,
                'config' => json_encode(['app_id' => '', 'secret_key' => '', 'env' => 'TEST']),
                'sort_order' => 3,
            ],
            [
                'name' => 'instamojo',
                'label' => 'Instamojo',
                'driver' => 'instamojo',
                'is_active' => false,
                'is_default' => false,
                'config' => json_encode(['api_key' => '', 'auth_token' => '', 'env' => 'TEST']),
                'sort_order' => 4,
            ],
            [
                'name' => 'payu',
                'label' => 'PayU',
                'driver' => 'payu',
                'is_active' => false,
                'is_default' => false,
                'config' => json_encode(['merchant_key' => '', 'merchant_salt' => '', 'env' => 'TEST']),
                'sort_order' => 5,
            ],
            [
                'name' => 'cod',
                'label' => 'Cash on Delivery',
                'driver' => 'cod',
                'is_active' => true,
                'is_default' => false,
                'config' => json_encode(['charge' => 0]),
                'sort_order' => 6,
            ]
        ];

        foreach ($methods as $method) {
            DB::table('payment_methods')->updateOrInsert(
                ['name' => $method['name']],
                $method
            );
        }
    }

    public function down(): void
    {
        DB::table('payment_methods')->whereIn('name', ['razorpay', 'phonepe', 'cashfree', 'instamojo', 'payu', 'cod'])->delete();
    }
};

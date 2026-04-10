<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop legacy razorpay columns if they exist
            if (Schema::hasColumn('orders', 'razorpay_order_id')) {
                $table->dropColumn('razorpay_order_id');
            }
            if (Schema::hasColumn('orders', 'razorpay_payment_id')) {
                $table->dropColumn('razorpay_payment_id');
            }

            // Create new generic columns
            if (!Schema::hasColumn('orders', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('orders', 'gateway_payment_id')) {
                $table->string('gateway_payment_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'gateway_order_id')) {
                $table->dropColumn('gateway_order_id');
            }
            if (Schema::hasColumn('orders', 'gateway_payment_id')) {
                $table->dropColumn('gateway_payment_id');
            }

            if (!Schema::hasColumn('orders', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable();
            }
        });
    }
};

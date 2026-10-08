<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('influencers')) {
            Schema::create('influencers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('coupon_id')->constrained('coupons')->onDelete('restrict');
                $table->string('name');
                $table->string('phone', 32)->nullable();
                $table->string('instagram_handle', 128)->nullable();
                $table->enum('commission_type', ['percent', 'flat'])->default('percent');
                $table->decimal('commission_value', 10, 2)->default(10.00);
                $table->string('upi_id', 128)->nullable();
                $table->json('bank_details')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('user_id');
                $table->index('coupon_id');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('influencer_commissions')) {
            Schema::create('influencer_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('influencer_id')->constrained('influencers')->onDelete('cascade');
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->string('coupon_code', 64);
                $table->decimal('order_subtotal', 10, 2)->default(0.00);
                $table->decimal('commission_rate', 10, 2)->default(0.00);
                $table->decimal('commission_amount', 10, 2)->default(0.00);
                $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
                $table->string('payout_reference', 255)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index('influencer_id');
                $table->index('order_id');
                $table->index('coupon_code');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencer_commissions');
        Schema::dropIfExists('influencers');
    }
};

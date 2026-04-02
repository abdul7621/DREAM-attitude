<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('type', 16);
            $table->decimal('value', 12, 2);
            $table->decimal('min_subtotal', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('coupon_code_snapshot')->nullable()->after('coupon_id');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('gclid')->nullable()->index();
            $table->string('fbclid')->nullable()->index();
        });

        Schema::create('shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 32);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('config');
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('carrier', 32)->default('manual');
            $table->string('awb')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 512)->unique();
            $table->string('to_path', 512);
            $table->unsignedSmallInteger('http_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug', 190)->unique();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 512)->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reviewer_name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('verified_purchase')->default(false);
            $table->timestamps();
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason');
            $table->json('images')->nullable();
            $table->string('status', 32)->default('requested');
            $table->string('resolution', 32)->nullable();
            $table->decimal('store_credit_amount', 12, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 128)->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('source', 32);
            $table->string('filename')->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('stats')->nullable();
            $table->longText('error_log')->nullable();
            $table->timestamps();
        });

        Schema::create('store_credit_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('store_credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('type', 32);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('abandoned_reminder_sent_at')->nullable();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32);
            $table->string('event', 64);
            $table->string('to_address')->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 32)->default('sent');
            $table->text('error')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'abandoned_reminder_sent_at']);
        });
        Schema::dropIfExists('store_credit_ledger');
        Schema::dropIfExists('store_credit_balances');
        Schema::dropIfExists('import_jobs');
        Schema::dropIfExists('recently_viewed');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_rules');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'coupon_id', 'coupon_code_snapshot', 'utm_source', 'utm_medium', 'utm_campaign',
                'utm_content', 'utm_term', 'gclid', 'fbclid',
            ]);
        });
        Schema::dropIfExists('coupons');
    }
};

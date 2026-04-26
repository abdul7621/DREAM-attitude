<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Visitors (unique browsers) ─────────────────────────
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_uuid', 36)->unique(); // da_vid cookie
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_source', 100)->nullable();
            $table->string('first_medium', 100)->nullable();
            $table->string('first_campaign', 255)->nullable();
            $table->string('device_type', 20)->nullable(); // mobile, desktop, tablet
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('country', 2)->nullable();
            $table->unsignedInteger('total_sessions')->default(0);
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('first_seen_at');
        });

        // ── Sessions (each visit) ──────────────────────────────
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->char('session_uuid', 36)->unique(); // da_sid cookie
            $table->foreignId('visitor_id')->constrained('visitors')->cascadeOnDelete();
            $table->string('source', 100)->nullable();
            $table->string('medium', 100)->nullable();
            $table->string('campaign', 255)->nullable();
            $table->string('landing_page', 500)->nullable();
            $table->string('exit_page', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->unsignedSmallInteger('page_count')->default(0);
            $table->unsignedSmallInteger('event_count')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->boolean('is_bounce')->default(true);
            // Funnel flags
            $table->boolean('reached_product')->default(false);
            $table->boolean('reached_cart')->default(false);
            $table->boolean('reached_checkout')->default(false);
            $table->boolean('reached_purchase')->default(false);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->useCurrent();
            $table->timestamps();

            $table->index(['visitor_id', 'started_at']);
            $table->index('started_at');
        });

        // ── Events (every tracked action) ──────────────────────
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('analytics_sessions')->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained('visitors')->cascadeOnDelete();
            $table->string('event_name', 50); // strict taxonomy
            $table->string('page_url', 500)->nullable();
            $table->string('page_type', 30)->nullable(); // home, product, category, cart, checkout, search, page
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->json('meta')->nullable(); // scroll %, search query, click target, revenue etc.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_name', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index('session_id');
        });

        // ── Product Metrics Daily (pre-aggregated) ─────────────
        Schema::create('product_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('add_to_cart')->default(0);
            $table->unsignedInteger('checkouts')->default(0);
            $table->unsignedInteger('purchases')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->unsignedInteger('searches')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_metrics_daily');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_sessions');
        Schema::dropIfExists('visitors');
    }
};

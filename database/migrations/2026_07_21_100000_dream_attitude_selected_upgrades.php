<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Dream Attitude Targeted Upgrades.
     */
    public function up(): void
    {
        // 1. Search Synonyms Table
        if (!Schema::hasTable('search_synonyms')) {
            Schema::create('search_synonyms', function (Blueprint $table) {
                $table->id();
                $table->string('term')->unique();
                $table->string('replace_with');
                $table->timestamps();
            });
        }

        // 2. Reviews Table Extensions
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'skin_type')) {
                $table->string('skin_type')->nullable()->after('rating');
            }
            if (!Schema::hasColumn('reviews', 'hair_type')) {
                $table->string('hair_type')->nullable()->after('skin_type');
            }
            if (!Schema::hasColumn('reviews', 'helpful_count')) {
                $table->integer('helpful_count')->default(0)->after('is_approved');
            }
            if (!Schema::hasColumn('reviews', 'seller_reply')) {
                $table->text('seller_reply')->nullable()->after('helpful_count');
            }
            if (!Schema::hasColumn('reviews', 'photos')) {
                $table->json('photos')->nullable()->after('seller_reply');
            }
        });

        // 3. Review Votes Table
        if (!Schema::hasTable('review_votes')) {
            Schema::create('review_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('review_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('session_id')->nullable();
                $table->timestamps();

                $table->unique(['review_id', 'user_id']);
                $table->unique(['review_id', 'session_id']);
            });
        }

        // 4. Return Requests Type Upgrade
        Schema::table('return_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('return_requests', 'type')) {
                $table->string('type')->default('refund')->after('status');
            }
        });

        // 5. Coupons Scoping Upgrade
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'applicable_type')) {
                $table->string('applicable_type')->default('all')->after('is_active');
            }
            if (!Schema::hasColumn('coupons', 'applicable_ids')) {
                $table->json('applicable_ids')->nullable()->after('applicable_type');
            }
            if (!Schema::hasColumn('coupons', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('applicable_ids')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('search_synonyms');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['skin_type', 'hair_type', 'helpful_count', 'seller_reply', 'photos']);
        });

        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['applicable_type', 'applicable_ids', 'user_id']);
        });
    }
};

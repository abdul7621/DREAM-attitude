<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Search Synonyms table
        Schema::create('search_synonyms', function (Blueprint $table) {
            $table->id();
            $table->string('term')->unique();
            $table->string('replace_with');
            $table->timestamps();
        });

        // 2. Add columns to reviews table
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('hair_type')->nullable()->after('verified_purchase');
            $table->string('skin_type')->nullable()->after('hair_type');
            $table->unsignedInteger('helpful_count')->default(0)->after('skin_type');
            $table->text('seller_reply')->nullable()->after('helpful_count');
        });

        // 3. Review Votes table
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->string('vote_type', 16)->default('up');
            $table->timestamps();
            $table->unique(['user_id', 'review_id']);
        });

        // 4. Add type column to return_requests table
        Schema::table('return_requests', function (Blueprint $table) {
            $table->string('type', 32)->default('refund')->after('user_id');
        });

        // 5. Add user_id to coupons table
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('is_active')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::dropIfExists('review_votes');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['hair_type', 'skin_type', 'helpful_count', 'seller_reply']);
        });

        Schema::dropIfExists('search_synonyms');
    }
};

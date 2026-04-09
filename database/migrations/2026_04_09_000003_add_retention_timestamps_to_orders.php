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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('review_request_sent_at')->nullable()->after('placed_at');
            $table->timestamp('replenishment_reminder_sent_at')->nullable()->after('review_request_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['review_request_sent_at', 'replenishment_reminder_sent_at']);
        });
    }
};

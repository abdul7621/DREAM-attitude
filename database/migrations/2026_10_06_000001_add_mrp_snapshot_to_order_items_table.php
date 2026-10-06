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
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'mrp_snapshot')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('mrp_snapshot', 10, 2)->nullable()->after('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'mrp_snapshot')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('mrp_snapshot');
            });
        }
    }
};

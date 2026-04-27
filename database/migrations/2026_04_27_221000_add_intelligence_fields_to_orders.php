<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('recovered_from_cart')->default(false)->after('order_status');
            $table->string('lead_source')->nullable()->after('recovered_from_cart');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['recovered_from_cart', 'lead_source']);
        });
    }
};

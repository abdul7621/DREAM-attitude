<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->tinyInteger('gst_rate')->default(0);
            $table->boolean('gst_inclusive')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['gst_amount', 'gst_rate', 'gst_inclusive']);
        });
    }
};

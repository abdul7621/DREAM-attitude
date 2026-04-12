<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipment_id')->nullable();
            $table->string('awb_number')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('tracking_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipment_id', 'awb_number', 'courier_name', 'tracking_url']);
        });
    }
};

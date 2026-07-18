<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3)->index(); // e.g. USA, GBR, IND
            $table->string('region_state', 255)->default('*');
            $table->string('zip_postal_code', 255)->default('*');
            $table->decimal('weight', 10, 4); // in kg
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['country_code', 'region_state', 'zip_postal_code', 'weight'], 'shipping_rates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};

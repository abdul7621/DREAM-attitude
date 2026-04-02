<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Default');
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable();
            $table->string('option1')->nullable();
            $table->string('option2')->nullable();
            $table->string('option3')->nullable();
            $table->decimal('price_retail', 12, 2);
            $table->decimal('price_reseller', 12, 2)->nullable();
            $table->decimal('price_bulk', 12, 2)->nullable();
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->integer('stock_qty')->default(0);
            $table->unsignedInteger('weight_grams')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_id');
            $table->unique('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug', 190)->unique();
            $table->string('sku')->nullable()->index();
            $table->string('short_description', 512)->nullable();
            $table->longText('description')->nullable();
            $table->string('brand')->nullable();
            $table->json('tags')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->unsignedInteger('sales_count')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 512)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

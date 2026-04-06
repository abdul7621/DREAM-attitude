<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('driver')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('seo_description');
            $table->json('layout_config')->nullable()->after('meta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta', 'layout_config']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');                     // Admin reference
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            // Hero Section
            $table->string('hero_headline');              // "Hair fall ruk nahi raha?"
            $table->string('hero_subheadline')->nullable();
            $table->string('hero_image')->nullable();     // Storage path
            $table->string('hero_cta_text')->default('Abhi Order Karo');

            // Pain Points (JSON array of strings)
            $table->json('problem_points')->nullable();

            // Product Steps (JSON array of {title, desc, image})
            $table->json('steps')->nullable();

            // Trust (numbers bar — rendered inline)
            $table->json('trust_points')->nullable();

            // Pricing
            $table->decimal('offer_price', 10, 2);        // 799
            $table->decimal('original_price', 10, 2)->nullable(); // 1299
            $table->string('offer_badge')->nullable();     // "Summer Sale"
            $table->boolean('show_cod_badge')->default(true);
            $table->boolean('show_free_ship')->default(true);

            // Products to add to cart (JSON array of {product_id, variant_id, qty})
            $table->json('products');

            // Reviews (JSON array of {name, rating, text, photo})
            $table->json('reviews')->nullable();

            // FAQ (JSON array of {q, a})
            $table->json('faq')->nullable();

            // WhatsApp
            $table->string('whatsapp_number')->nullable(); // 8141939616

            // Trust description (NR Beauty World paragraph)
            $table->text('trust_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};

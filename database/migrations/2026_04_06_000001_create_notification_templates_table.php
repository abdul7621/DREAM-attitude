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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., order_confirmation, order_shipped, abandoned_cart
            $table->string('channel'); // email, whatsapp, sms
            $table->string('subject')->nullable(); // mostly for email
            $table->text('body'); // the actual template content using {variables}
            $table->json('variables_guide')->nullable(); // array of variables allowed for this template
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};

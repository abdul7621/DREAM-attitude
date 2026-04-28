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
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'lead_status')) {
                $table->string('lead_status')->default('New')->after('abandoned_reminder_step');
            }
            if (!Schema::hasColumn('carts', 'lead_notes')) {
                $table->text('lead_notes')->nullable()->after('lead_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['lead_status', 'lead_notes']);
        });
    }
};

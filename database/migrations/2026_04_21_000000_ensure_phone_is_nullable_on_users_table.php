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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible logic if needed, but going backwards to NOT NULL might crash.
        // It's safer to leave as is or drop the change.
        // Schema::table('users', function (Blueprint $table) {
        //     $table->string('phone', 32)->nullable(false)->change();
        // });
    }
};

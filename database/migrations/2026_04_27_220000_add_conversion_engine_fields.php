<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('guest_phone', 32)->nullable()->after('currency');
            $table->string('lead_source')->nullable()->after('guest_phone');
            $table->timestamp('captured_at')->nullable()->after('lead_source');
            $table->string('offer_claimed')->nullable()->after('captured_at');
            $table->integer('abandoned_reminder_step')->default(0)->after('offer_claimed');
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->string('normalized_phone', 32)->nullable()->after('id');
            $table->string('phone_hash')->nullable()->index()->after('normalized_phone');
            $table->string('first_capture_source')->nullable()->after('phone_hash');
            $table->timestamp('last_capture_at')->nullable()->after('first_capture_source');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn([
                'guest_phone',
                'lead_source',
                'captured_at',
                'offer_claimed',
                'abandoned_reminder_step',
            ]);
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn([
                'normalized_phone',
                'phone_hash',
                'first_capture_source',
                'last_capture_at',
            ]);
        });
    }
};

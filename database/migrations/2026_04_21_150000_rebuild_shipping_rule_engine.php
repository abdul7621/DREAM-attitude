<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ShippingRule;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Backup legacy rules directly inside the migration
        try {
            if (Schema::hasTable('shipping_rules')) {
                $rules = DB::table('shipping_rules')->get();
                if ($rules->isNotEmpty()) {
                    file_put_contents(
                        storage_path('legacy_shipping_rules_backup_' . time() . '.json'),
                        $rules->toJson(JSON_PRETTY_PRINT)
                    );
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not write legacy shipping rules backup: ' . $e->getMessage());
        }

        // 2. Create Pincode Cache Table
        Schema::create('pincode_caches', function (Blueprint $table) {
            $table->id();
            $table->string('postal_code', 16)->unique();
            $table->string('city', 128)->nullable();
            $table->string('state', 128)->nullable();
            $table->timestamps();
        });

        // 3. Drop old fields from shipping_rules
        Schema::table('shipping_rules', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_rules', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('shipping_rules', 'config')) {
                $table->dropColumn('config');
            }
        });

        // 4. Create new advanced rule tables
        Schema::create('shipping_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('shipping_rules')->cascadeOnDelete();
            $table->string('type', 64); // order_value, state, city, pincode_prefix, payment_method, weight
            $table->string('operator', 16); // ==, !=, in, >, <, >=, <=, not_in
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('shipping_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->unique()->constrained('shipping_rules')->cascadeOnDelete();
            $table->string('type', 32); // flat, free
            $table->decimal('value', 12, 2)->default(0);
            $table->timestamps();
        });

        // 5. Seed default fallback rule
        DB::transaction(function() {
            // Delete all existing old format rules to prevent broken relations or logic faults
            DB::table('shipping_rules')->truncate();

            // Insert new default fallback
            $ruleId = DB::table('shipping_rules')->insertGetId([
                'name' => 'Default Standard Shipping',
                'priority' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('shipping_actions')->insert([
                'rule_id' => $ruleId,
                'type' => 'flat',
                'value' => 89.00,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // No conditions inserted means this rule ALWAYS applies.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_actions');
        Schema::dropIfExists('shipping_conditions');
        Schema::dropIfExists('pincode_caches');
        
        Schema::table('shipping_rules', function (Blueprint $table) {
            $table->string('type', 32)->default('flat');
            $table->json('config')->nullable();
        });
    }
};

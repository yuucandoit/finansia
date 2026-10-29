<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('future_plans', function (Blueprint $table) {
            $table->string('share_code', 32)->nullable()->unique()->after('color');
        });

        Schema::create('future_plan_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('future_plan_id')->constrained('future_plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)->default('editor'); // owner, editor, viewer
            $table->string('status', 30)->default('active'); // active, pending
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['future_plan_id', 'user_id']);
            $table->index(['future_plan_id', 'last_active_at']);
        });

        // Generate unique share_code & daftarkan owner untuk data future_plans yang sudah ada
        $existingPlans = DB::table('future_plans')->whereNull('share_code')->get();
        foreach ($existingPlans as $plan) {
            $code = 'FP-' . strtoupper(Str::random(6));
            DB::table('future_plans')->where('id', $plan->id)->update(['share_code' => $code]);

            DB::table('future_plan_members')->insertOrIgnore([
                'future_plan_id' => $plan->id,
                'user_id'        => $plan->user_id,
                'role'           => 'owner',
                'status'         => 'active',
                'last_active_at' => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('future_plan_members');

        Schema::table('future_plans', function (Blueprint $table) {
            $table->dropColumn('share_code');
        });
    }
};


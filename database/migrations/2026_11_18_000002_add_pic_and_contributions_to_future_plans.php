<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('future_plan_items', function (Blueprint $table) {
            $table->foreignId('assigned_to')
                ->nullable()
                ->after('transaction_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::create('future_plan_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('future_plan_id')->constrained('future_plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('contributed_at');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('future_plan_contributions');

        Schema::table('future_plan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};

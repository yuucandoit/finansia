<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('future_plans', function (Blueprint $table) {
            $table->foreignId('saving_id')
                ->nullable()
                ->after('user_id')
                ->constrained('savings')
                ->nullOnDelete();
        });

        Schema::table('future_plan_items', function (Blueprint $table) {
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('is_completed')
                ->constrained('transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('future_plan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_id');
        });

        Schema::table('future_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saving_id');
        });
    }
};

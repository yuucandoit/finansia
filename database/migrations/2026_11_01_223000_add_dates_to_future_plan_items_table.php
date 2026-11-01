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
        Schema::table('future_plan_items', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('notes');
            $table->date('end_date')->nullable()->after('start_date');

            $table->index(['future_plan_id', 'start_date', 'end_date']);
        });

        Schema::table('future_plans', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('future_plans', function (Blueprint $table) {
            $table->dropColumn('start_date');
        });

        Schema::table('future_plan_items', function (Blueprint $table) {
            $table->dropIndex(['future_plan_id', 'start_date', 'end_date']);
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};


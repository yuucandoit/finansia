<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('future_plan_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('future_plan_id')->constrained('future_plans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('description');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['future_plan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('future_plan_activities');
    }
};

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
        Schema::create('future_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('future_plan_id')->constrained('future_plans')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('future_plan_items')->cascadeOnDelete();
            $table->string('title');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['future_plan_id', 'parent_id']);
            $table->index(['future_plan_id', 'is_completed']);
            $table->index(['future_plan_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('future_plan_items');
    }
};


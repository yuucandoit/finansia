<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('budget_item_id')->nullable()->constrained('budget_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['transaction_id', 'category_id']);
            $table->index(['budget_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_splits');
    }
};

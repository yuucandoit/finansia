<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('last_four', 4)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->unsignedTinyInteger('billing_day')->default(1);
            $table->unsignedTinyInteger('due_day')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('credit_card_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_card_id')->constrained('credit_cards')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('monthly_amount', 15, 2);
            $table->unsignedSmallInteger('total_months');
            $table->unsignedSmallInteger('paid_months')->default(0);
            $table->date('start_date');
            $table->string('status', 20)->default('active'); // active | paid
            $table->timestamps();

            $table->index(['credit_card_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_installments');
        Schema::dropIfExists('credit_cards');
    }
};

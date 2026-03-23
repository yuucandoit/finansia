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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('savings_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('type');
            $table->text('description');
            $table->decimal('amount', 16, 2);
            $table->decimal('fee', 16, 2)->nullable();
            $table->boolean('is_manual')->default(false);
            $table->date('transaction_date');
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id');
            $table->string('name');
            $table->integer('quantity');
            $table->string('unit');
            $table->decimal('total', 16, 2);
            $table->decimal('grand_total', 16, 2);
            $table->timestamp('tr_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction__items');
    }
};

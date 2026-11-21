<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('asset_type', 40)->default('gold');
            $table->string('unit_label', 30)->default('unit');
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('buy_price_per_unit', 15, 2)->default(0);
            $table->decimal('current_price_per_unit', 15, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamp('last_valued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

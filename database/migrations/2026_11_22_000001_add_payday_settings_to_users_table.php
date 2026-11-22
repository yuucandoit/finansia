<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('payday_day')->default(1)->after('plan_expires_at');
            $table->boolean('payday_weekend_adjust')->default(false)->after('payday_day');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payday_day', 'payday_weekend_adjust']);
        });
    }
};

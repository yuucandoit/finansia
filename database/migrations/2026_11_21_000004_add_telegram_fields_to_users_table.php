<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id', 64)->nullable()->unique()->after('status');
            $table->string('telegram_link_code', 32)->nullable()->unique()->after('telegram_chat_id');
            $table->boolean('telegram_notifications_enabled')->default(true)->after('telegram_link_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_link_code',
                'telegram_notifications_enabled',
            ]);
        });
    }
};

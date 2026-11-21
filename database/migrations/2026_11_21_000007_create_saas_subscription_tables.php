<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan_tier', 20)->default('free')->after('telegram_notifications_enabled');
            $table->timestamp('plan_expires_at')->nullable()->after('plan_tier');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_tier', 20)->default('pro');
            $table->string('billing_cycle', 20)->default('monthly'); // monthly | yearly
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending | active | cancelled | expired
            $table->string('order_id', 64)->unique();
            $table->string('payment_provider', 30)->default('sandbox');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('feature', 40)->default('chat'); // chat | ocr | future_plan
            $table->string('model', 80)->nullable();
            $table->integer('tokens_estimated')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan_tier', 'plan_expires_at']);
        });
    }
};

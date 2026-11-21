<?php

use App\Models\AiUsageLog;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view pricing, checkout in sandbox, and webhook activates pending subscription', function () {
    config()->set('saas.sandbox_instant_activate', false);
    config()->set('saas.billing_webhook_secret', 'wh-secret-999');

    $user = User::factory()->create(['plan_tier' => 'free']);

    $this->actingAs($user)
        ->get(route('pricing.index'))
        ->assertStatus(200)
        ->assertSee('Finansia Pro')
        ->assertSee('Starter (Gratis)');

    // Create pending subscription
    $this->actingAs($user)
        ->post(route('billing.checkout'), ['billing_cycle' => 'monthly'])
        ->assertRedirect(route('pricing.index'));

    $sub = Subscription::where('user_id', $user->id)->first();
    expect($sub)->not->toBeNull()
        ->and($sub->status)->toBe('pending')
        ->and($user->fresh()->isPro())->toBeFalse();

    // Webhook rejects invalid secret
    $this->postJson('/api/billing/webhook', [
        'order_id' => $sub->order_id,
        'status'   => 'paid',
    ], ['X-Billing-Webhook-Secret' => 'wrong'])
        ->assertStatus(403);

    // Webhook activates Pro
    $this->postJson('/api/billing/webhook', [
        'order_id' => $sub->order_id,
        'status'   => 'paid',
    ], ['X-Billing-Webhook-Secret' => 'wh-secret-999'])
        ->assertStatus(200)
        ->assertJsonPath('sub_status', 'active');

    expect($user->fresh()->isPro())->toBeTrue()
        ->and($user->fresh()->plan_tier)->toBe('pro');
});

test('ai quota blocks free user when limit reached and enforcement is enabled, while pro is unlimited', function () {
    config()->set('saas.enforcement_enabled', true);
    config()->set('saas.plans.free.monthly_ai_limit', 2);

    $freeUser = User::factory()->create(['plan_tier' => 'free']);
    $proUser = User::factory()->create(['plan_tier' => 'pro', 'plan_expires_at' => now()->addMonth()]);

    AiUsageLog::create(['user_id' => $freeUser->id, 'feature' => 'chat', 'tokens_estimated' => 10]);
    AiUsageLog::create(['user_id' => $freeUser->id, 'feature' => 'chat', 'tokens_estimated' => 10]);

    expect($freeUser->canUseAi())->toBeFalse()
        ->and($freeUser->remainingAiQuota())->toBe(0)
        ->and($proUser->canUseAi())->toBeTrue()
        ->and($proUser->remainingAiQuota())->toBeNull();

    $this->actingAs($freeUser)
        ->postJson(route('ai.parse'), ['message' => 'Halo Finansia'])
        ->assertStatus(429)
        ->assertJsonPath('status', 'error');
});

test('admin saas panel is restricted to master_mind and can toggle user tier', function () {
    $regular = User::factory()->create(['role' => 'user', 'plan_tier' => 'free']);
    $admin = User::factory()->create(['role' => 'admin', 'plan_tier' => 'free']);

    // Admin automatically counts as Pro
    expect($admin->isPro())->toBeTrue();

    // Non-admin blocked
    $this->actingAs($regular)
        ->get(route('admin.saas.index'))
        ->assertStatus(403);

    // Admin views SaaS dashboard & grants Pro to regular user
    $this->actingAs($admin)
        ->get(route('admin.saas.index'))
        ->assertStatus(200)
        ->assertSee('Admin SaaS')
        ->assertSee('Estimasi MRR');

    $this->actingAs($admin)
        ->patch(route('admin.saas.users.toggle-tier', $regular))
        ->assertRedirect(route('admin.saas.index'));

    expect($regular->fresh()->plan_tier)->toBe('pro')
        ->and($regular->fresh()->isPro())->toBeTrue();
});

<?php

use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Debt;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('user can generate link code, link telegram via webhook, and quick-record transaction with smart rules', function () {
    Http::fake();
    config()->set('services.telegram.bot_token', 'test-bot-token');
    config()->set('services.telegram.webhook_secret', 'secret-123');

    $user = User::factory()->create(['name' => 'Alya']);
    $savingBca = Saving::factory()->create([
        'user_id' => $user->id,
        'name' => 'BCA Utama',
        'current_balance' => 1_000_000,
    ]);

    $catCoffee = Category::create([
        'user_id' => $user->id,
        'name' => 'Kopi & Nongkrong',
        'type' => 'Cash Out',
    ]);
    CategoryRule::create([
        'user_id' => $user->id,
        'keyword' => 'kopi kenangan',
        'category_id' => $catCoffee->id,
        'is_active' => true,
    ]);

    // 1. Generate link code from profile
    $this->actingAs($user)
        ->post(route('profile.telegram.code'))
        ->assertRedirect(route('profile.edit'));

    $code = $user->fresh()->telegram_link_code;
    expect($code)->toStartWith('FIN-');

    // 2. Webhook rejects wrong secret
    $this->postJson('/api/telegram/webhook', [
        'message' => ['chat' => ['id' => 998877], 'text' => "/link {$code}"],
    ], ['X-Telegram-Bot-Api-Secret-Token' => 'wrong'])
        ->assertStatus(403);

    // 3. Webhook links account
    $this->postJson('/api/telegram/webhook', [
        'message' => ['chat' => ['id' => 998877], 'text' => "/link {$code}"],
    ], ['X-Telegram-Bot-Api-Secret-Token' => 'secret-123'])
        ->assertStatus(200)
        ->assertJsonPath('result.action', 'linked');

    expect($user->fresh()->telegram_chat_id)->toBe('998877')
        ->and($user->fresh()->telegram_link_code)->toBeNull();

    // 4. Quick-record Cash Out via chat
    $this->postJson('/api/telegram/webhook', [
        'message' => ['chat' => ['id' => 998877], 'text' => 'Kopi Kenangan 28rb BCA Utama'],
    ], ['X-Telegram-Bot-Api-Secret-Token' => 'secret-123'])
        ->assertStatus(200)
        ->assertJsonPath('result.action', 'recorded');

    $tx = Transaction::where('user_id', $user->id)->first();
    expect($tx)->not->toBeNull()
        ->and((float) $tx->amount)->toBe(28_000.0)
        ->and($tx->type)->toBe('Cash Out')
        ->and($tx->category_id)->toBe($catCoffee->id)
        ->and((float) $savingBca->fresh()->current_balance)->toBe(972_000.0);

    // 5. /saldo & /ringkasan commands
    $this->postJson('/api/telegram/webhook', [
        'message' => ['chat' => ['id' => 998877], 'text' => '/saldo'],
    ], ['X-Telegram-Bot-Api-Secret-Token' => 'secret-123'])
        ->assertStatus(200)
        ->assertJsonPath('result.action', 'balance');

    $this->postJson('/api/telegram/webhook', [
        'message' => ['chat' => ['id' => 998877], 'text' => '/ringkasan'],
    ], ['X-Telegram-Bot-Api-Secret-Token' => 'secret-123'])
        ->assertStatus(200)
        ->assertJsonPath('result.action', 'summary');
});

test('telegram daily reminder command sends reminders to connected users with alerts', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);
    config()->set('services.telegram.bot_token', 'test-bot-token');

    $user = User::factory()->create([
        'telegram_chat_id' => '554433',
        'telegram_notifications_enabled' => true,
    ]);

    Debt::create([
        'user_id' => $user->id,
        'type' => Debt::TYPE_PAYABLE,
        'person_name' => 'Rudi',
        'total_amount' => 250_000,
        'paid_amount' => 0,
        'due_date' => now()->subDay()->toDateString(),
        'status' => Debt::STATUS_UNPAID,
    ]);

    $this->artisan('telegram:send-reminders')
        ->assertSuccessful();

    Http::assertSentCount(1);
});

test('profile page renders telegram bot copilot card and unlink works', function () {
    $user = User::factory()->create([
        'telegram_chat_id' => '112233',
        'telegram_notifications_enabled' => true,
    ]);

    $this->actingAs($user)
        ->get('/profile')
        ->assertStatus(200)
        ->assertSee('Telegram Bot Copilot')
        ->assertSee('Terhubung');

    $this->actingAs($user)
        ->delete(route('profile.telegram.unlink'))
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->telegram_chat_id)->toBeNull();
});

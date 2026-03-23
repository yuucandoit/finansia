<?php

use App\Models\User;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
//  AUTH GUARD TESTS
// ─────────────────────────────────────────────

test('dashboard redirects unauthenticated user to login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('dashboard');
});

// ─────────────────────────────────────────────
//  DATA INTEGRITY TESTS
// ─────────────────────────────────────────────

test('dashboard shows correct total balance from all savings', function () {
    $user = User::factory()->create();

    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);
    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 3_000_000]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewHas('totalBalance', 8_000_000);
});

test('dashboard only shows data belonging to authenticated user', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    Saving::factory()->create(['user_id' => $user->id,      'current_balance' => 2_000_000]);
    Saving::factory()->create(['user_id' => $otherUser->id, 'current_balance' => 9_000_000]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertViewHas('totalBalance', 2_000_000);
});

test('dashboard calculates cash-in this month correctly', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    // Transaksi bulan ini
    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash In',
        'amount'           => 3_000_000,
        'transaction_date' => now()->startOfMonth()->addDays(2),
    ]);

    // Transaksi bulan lalu — tidak boleh ikut dihitung
    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash In',
        'amount'           => 1_000_000,
        'transaction_date' => now()->subMonth(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertViewHas('cashInThisMonth', 3_000_000);
});

test('dashboard calculates cash-out this month correctly', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash Out',
        'amount'           => 500_000,
        'transaction_date' => now(),
    ]);
    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Transfer',
        'amount'           => 200_000,
        'transaction_date' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertViewHas('cashOutThisMonth', 700_000);
});

// ─────────────────────────────────────────────
//  RESPONSE TIME / PERFORMANCE TESTS
// ─────────────────────────────────────────────

test('dashboard response time is acceptable under 500ms with moderate data', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    // Buat 100 transaksi
    Transaction::factory(100)->create([
        'user_id'    => $user->id,
        'savings_id' => $saving->id,
    ]);

    $start    = microtime(true);
    $response = $this->actingAs($user)->get('/dashboard');
    $elapsed  = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);

    expect($elapsed)->toBeLessThan(500)
        ->and($elapsed)->toBeGreaterThan(0);
})->note('Baseline: standard Laravel, no Octane');

test('dashboard handles user with zero savings gracefully', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewHas('totalBalance', 0);
    $response->assertViewHas('cashInThisMonth', 0);
    $response->assertViewHas('cashOutThisMonth', 0);
});

// ─────────────────────────────────────────────
//  VIEW DATA STRUCTURE TESTS
// ─────────────────────────────────────────────

test('dashboard view contains required chart data variables', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertViewHasAll([
        'totalBalance',
        'cashInThisMonth',
        'cashOutThisMonth',
        'last7Days',
        'cashInData',
        'cashOutData',
        'expenseLabels',
        'expenseData',
        'last30Days',
        'balanceTrend',
        'recentTransactions',
    ]);
});

test('last7Days array has exactly 7 entries', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');
    $last7Days = $response->viewData('last7Days');

    expect($last7Days)->toHaveCount(7);
});

test('last30Days array has exactly 30 entries', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');
    $last30Days = $response->viewData('last30Days');

    expect($last30Days)->toHaveCount(30);
});

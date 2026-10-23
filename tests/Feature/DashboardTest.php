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

test('internal transfers between own accounts are excluded from cash in and cash out', function () {
    $user = User::factory()->create();
    $source = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);
    $dest = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 2_000_000]);

    $groupId = (string) \Illuminate\Support\Str::uuid();

    // 1. Kaki keluar (Transfer dari source ke dest)
    Transaction::create([
        'user_id'           => $user->id,
        'savings_id'        => $source->id,
        'type'              => 'Transfer',
        'direction'         => Transaction::DIRECTION_OUT,
        'transfer_group_id' => $groupId,
        'description'       => 'Pindah dana (ke Rekening B)',
        'amount'            => 1_000_000,
        'fee'               => 2_500,
        'transaction_date'  => now(),
    ]);

    // 2. Kaki masuk (Transfer masuk di dest)
    Transaction::create([
        'user_id'           => $user->id,
        'savings_id'        => $dest->id,
        'type'              => 'Transfer',
        'direction'         => Transaction::DIRECTION_IN,
        'transfer_group_id' => $groupId,
        'description'       => 'Pindah dana (dari Rekening A)',
        'amount'            => 1_000_000,
        'fee'               => null,
        'transaction_date'  => now(),
    ]);

    // Pemasukan riil & Pengeluaran riil
    Transaction::create([
        'user_id'           => $user->id,
        'savings_id'        => $source->id,
        'type'              => 'Cash In',
        'direction'         => Transaction::DIRECTION_IN,
        'description'       => 'Gaji',
        'amount'            => 5_000_000,
        'transaction_date'  => now(),
    ]);

    Transaction::create([
        'user_id'           => $user->id,
        'savings_id'        => $source->id,
        'type'              => 'Cash Out',
        'direction'         => Transaction::DIRECTION_OUT,
        'description'       => 'Belanja',
        'amount'            => 500_000,
        'transaction_date'  => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);

    // Cash In HANYA gaji (5.000.000), transfer masuk tidak ikut dihitung
    $response->assertViewHas('cashInThisMonth', 5_000_000.0);

    // Cash Out HANYA belanja (500.000) + fee transfer (2.500) = 502.500
    // Transfer 1.000.000 TIDAK dihitung sebagai pengeluaran!
    $response->assertViewHas('cashOutThisMonth', 502_500.0);

    $dashboardData = $response->viewData('dashboard');
    expect($dashboardData['cashflow']['internal_transfer'])->toEqual(1_000_000.0);
    expect($dashboardData['cashflow']['net']['amount'])->toEqual(5_000_000.0 - 502_500.0);
});

test('api dashboard endpoint returns structured cashflow and net worth metrics', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 3_000_000]);

    Transaction::create([
        'user_id'           => $user->id,
        'savings_id'        => $saving->id,
        'type'              => 'Cash In',
        'direction'         => 'in',
        'description'       => 'Project Freelance',
        'amount'            => 2_000_000,
        'transaction_date'  => now(),
    ]);

    $token = $user->createToken('test_dashboard_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/v1/dashboard');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'message',
        'data' => [
            'period',
            'net_worth' => ['current', 'start_of_month', 'change', 'change_pct'],
            'cashflow' => ['income', 'expense', 'savings_allocation', 'internal_transfer', 'net', 'savings_rate'],
            'spending_by_category',
            'uncategorized',
            'top_expenses',
            'accounts',
            'budget',
            'charts' => [
                'monthly_12',
                'daily_balance_30',
                'cash_flow_7_days',
            ],
            'recent_transactions',
        ]
    ]);

    $response->assertJsonPath('data.summary.total_balance', 3_000_000);
    $response->assertJsonPath('data.summary.cash_in_this_month', 2_000_000);
});


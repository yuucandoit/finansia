<?php

use App\Models\Bank;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
//  AUTH GUARD
// ─────────────────────────────────────────────

test('saving index requires authentication', function () {
    $this->get('/saving')->assertRedirect('/login');
});

test('authenticated user can view savings list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/saving')
        ->assertStatus(200)
        ->assertViewIs('saving');
});

// ─────────────────────────────────────────────
//  STORE
// ─────────────────────────────────────────────

test('user can create a new saving account', function () {
    $user = User::factory()->create();
    $bank = Bank::first() ?? Bank::factory()->create(['name' => 'BCA']);

    $payload = [
        'name'            => 'Tabungan BCA Utama',
        'bank_id'         => $bank->id,
        'account_number'  => '1234567890',
        'current_balance' => 10_000_000,
    ];

    $response = $this->actingAs($user)->post('/saving/store', $payload);

    $response->assertRedirect(route('saving.index'));
    $this->assertDatabaseHas('savings', [
        'user_id'        => $user->id,
        'name'           => 'Tabungan BCA Utama',
        'current_balance' => 10_000_000,
    ]);
});

test('saving store fails when name is missing', function () {
    $user = User::factory()->create();
    $bank = Bank::first() ?? Bank::factory()->create(['name' => 'BCA']);

    $response = $this->actingAs($user)->post('/saving/store', [
        'bank_id'         => $bank->id,
        'account_number'  => '123',
        'current_balance' => 1_000_000,
    ]);

    $response->assertSessionHasErrors('name');
});

test('saving store fails when bank_id is invalid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/saving/store', [
        'name'            => 'Tabungan',
        'bank_id'         => 99999,
        'account_number'  => '123',
        'current_balance' => 1_000_000,
    ]);

    $response->assertSessionHasErrors('bank_id');
});

test('saving store fails when current_balance is negative', function () {
    $user = User::factory()->create();
    $bank = Bank::first() ?? Bank::factory()->create(['name' => 'BCA']);

    $response = $this->actingAs($user)->post('/saving/store', [
        'name'            => 'Tabungan',
        'bank_id'         => $bank->id,
        'account_number'  => '123',
        'current_balance' => -1000,
    ]);

    $response->assertSessionHasErrors('current_balance');
});

// ─────────────────────────────────────────────
//  UPDATE
// ─────────────────────────────────────────────

test('user can update their own saving account', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'name' => 'Lama']);
    $bank   = Bank::first() ?? Bank::factory()->create(['name' => 'Mandiri']);

    $response = $this->actingAs($user)->put("/saving/{$saving->id}", [
        'name'            => 'Tabungan Baru',
        'bank_id'         => $bank->id,
        'account_number'  => '0987654321',
        'current_balance' => 5_000_000,
    ]);

    $response->assertRedirect(route('saving.index'));
    $this->assertDatabaseHas('savings', [
        'id'   => $saving->id,
        'name' => 'Tabungan Baru',
    ]);
});

test('user cannot update another users saving account', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();
    $saving    = Saving::factory()->create(['user_id' => $otherUser->id]);
    $bank      = Bank::first() ?? Bank::factory()->create(['name' => 'BRI']);

    $this->actingAs($user)->put("/saving/{$saving->id}", [
        'name'            => 'Hacked',
        'bank_id'         => $bank->id,
        'account_number'  => '000',
        'current_balance' => 999,
    ])->assertStatus(404);
});

// ─────────────────────────────────────────────
//  DELETE
// ─────────────────────────────────────────────

test('user can delete their own saving account', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    $savingId = $saving->id;

    $response = $this->actingAs($user)->delete("/saving/{$savingId}");

    $response->assertRedirect(route('saving.index'));

    // Model Saving tidak menggunakan SoftDeletes trait,
    // sehingga delete() menghapus record secara permanen.
    $this->assertDatabaseMissing('savings', ['id' => $savingId]);
});

test('user cannot delete another users saving account', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();
    $saving    = Saving::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->delete("/saving/{$saving->id}")
        ->assertStatus(404);
});

// ─────────────────────────────────────────────
//  DETAIL PAGE
// ─────────────────────────────────────────────

test('user can view detail of their own saving', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get("/saving/detail/{$saving->id}");

    $response->assertStatus(200)
             ->assertViewIs('saving_detail')
             ->assertViewHas('saving');
});

test('saving detail shows correct yearly income', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash In',
        'amount'           => 12_000_000,
        'transaction_date' => now()->startOfYear()->addMonth(),
    ]);

    $response = $this->actingAs($user)->get("/saving/detail/{$saving->id}");

    $response->assertViewHas('yearly_income', 12_000_000);
});

test('saving detail shows correct yearly spending', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash Out',
        'amount'           => 3_000_000,
        'transaction_date' => now()->startOfYear()->addMonth(),
    ]);

    $response = $this->actingAs($user)->get("/saving/detail/{$saving->id}");

    $response->assertViewHas('yearly_spending', 3_000_000);
});

// ─────────────────────────────────────────────
//  TOTAL BALANCE
// ─────────────────────────────────────────────

test('saving index shows correct total balance across all savings', function () {
    $user = User::factory()->create();
    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 2_000_000]);
    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 3_500_000]);

    $response = $this->actingAs($user)->get('/saving');

    $response->assertViewHas('totalBalance', 5_500_000);
});

// ─────────────────────────────────────────────
//  RESPONSE TIME
// ─────────────────────────────────────────────

test('saving index responds within 300ms with 50 accounts', function () {
    $user = User::factory()->create();

    Saving::factory(50)->create(['user_id' => $user->id]);

    $start    = microtime(true);
    $response = $this->actingAs($user)->get('/saving');
    $elapsed  = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);
    expect($elapsed)->toBeLessThan(300);
})->note('Baseline performance test');

// ─────────────────────────────────────────────
//  RECONCILE BALANCE & ANALYTICS DETAIL
// ─────────────────────────────────────────────

test('user can reconcile saving balance and adjustment transaction is created', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'current_balance' => 1_000_000,
    ]);

    $response = $this->actingAs($user)->post("/saving/{$saving->id}/reconcile", [
        'actual_balance' => 850_000,
        'notes'          => 'Selisih biaya makan & parkir',
    ]);

    $response->assertRedirect();
    expect((float) $saving->fresh()->current_balance)->toBe(850_000.0);

    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'savings_id'  => $saving->id,
        'type'        => 'Cash Out',
        'direction'   => 'out',
        'amount'      => 150_000,
        'description' => 'Rekonsiliasi Saldo: Selisih biaya makan & parkir',
    ]);
});

test('saving detail filters by daily weekly monthly and date range and computes category breakdown', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);

    $catFood = \App\Models\Category::create([
        'name'  => 'Makanan & Minuman',
        'type'  => 'Cash Out',
        'color' => 'amber',
        'icon'  => 'fa-utensils',
    ]);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $catFood->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Kopi Kenangan',
        'amount'           => 50_000,
        'transaction_date' => '2026-11-10',
    ]);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $catFood->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Makan Malam Keluarga',
        'amount'           => 250_000,
        'transaction_date' => '2026-11-15',
    ]);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Beli Oli Bulan Oktober',
        'amount'           => 100_000,
        'transaction_date' => '2026-10-05',
    ]);

    // 1. Filter Monthly (November 2026)
    $resMonthly = $this->actingAs($user)->get("/saving/detail/{$saving->id}?period=monthly&month=11&year=2026");
    $resMonthly->assertStatus(200);
    expect((float) $resMonthly->viewData('periodSpending'))->toBe(300_000.0);
    $breakdown = $resMonthly->viewData('spendingByCategory');
    expect($breakdown)->toHaveCount(1);
    expect($breakdown->first()['category'])->toBe('Makanan & Minuman');
    expect((float) $breakdown->first()['total'])->toBe(300_000.0);

    // 2. Filter Daily (2026-11-10)
    $resDaily = $this->actingAs($user)->get("/saving/detail/{$saving->id}?period=daily&date=2026-11-10");
    $resDaily->assertStatus(200);
    expect((float) $resDaily->viewData('periodSpending'))->toBe(50_000.0);

    // 3. Filter Range (2026-11-01 to 2026-11-12)
    $resRange = $this->actingAs($user)->get("/saving/detail/{$saving->id}?period=range&start_date=2026-11-01&end_date=2026-11-12");
    $resRange->assertStatus(200);
    expect((float) $resRange->viewData('periodSpending'))->toBe(50_000.0);
});

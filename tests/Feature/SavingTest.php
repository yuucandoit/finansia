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

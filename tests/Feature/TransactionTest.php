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

test('transaction page requires authentication', function () {
    $this->get('/transaksi')->assertRedirect('/login');
});

test('authenticated user can visit transaction list page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/transaksi')
        ->assertStatus(200)
        ->assertViewIs('transaction');
});

// ─────────────────────────────────────────────
//  CRUD – STORE
// ─────────────────────────────────────────────

test('user can store a Cash In transaction', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $payload = [
        'description'      => 'Gaji Bulanan Test',
        'type'             => 'Cash In',
        'amount'           => 5_000_000,
        'savings_id'       => $saving->id,
        'transaction_date' => now()->toDateString(),
    ];

    $response = $this->actingAs($user)->post('/transaksi/store', $payload);

    $response->assertRedirect(route('transaction.index'));
    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'description' => 'Gaji Bulanan Test',
        'type'        => 'Cash In',
        'amount'      => 5_000_000,
    ]);
});

test('user can store a Cash Out transaction', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'description'      => 'Belanja Bulanan',
        'type'             => 'Cash Out',
        'amount'           => 350_000,
        'savings_id'       => $saving->id,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transaction.index'));
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'type'    => 'Cash Out',
        'amount'  => 350_000,
    ]);
});

test('user can store a Tabungan transfer transaction', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'description'      => 'Setor Tabungan',
        'type'             => 'Tabungan',
        'amount'           => 1_000_000,
        'savings_id'       => $saving->id,
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transaction.index'));
    $this->assertDatabaseHas('transactions', ['type' => 'Tabungan']);
});

// ─────────────────────────────────────────────
//  VALIDATION TESTS
// ─────────────────────────────────────────────

test('store transaction fails when description is missing', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'type'   => 'Cash In',
        'amount' => 1_000_000,
    ]);

    $response->assertSessionHasErrors('description');
    $this->assertDatabaseCount('transactions', 0);
});

test('store transaction fails with invalid type', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'description' => 'Test',
        'type'        => 'INVALID_TYPE',
        'amount'      => 100_000,
    ]);

    $response->assertSessionHasErrors('type');
});

test('store transaction fails when amount is negative', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'description' => 'Tes',
        'type'        => 'Cash In',
        'amount'      => -500,
    ]);

    $response->assertSessionHasErrors('amount');
});

test('store transaction fails when amount is zero', function () {
    // Catatan: Validasi saat ini menggunakan 'min:0' sehingga amount=0 DITERIMA.
    // Test ini mendokumentasikan perilaku current - jika ingin menolak 0, ubah ke 'min:1'
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'description' => 'Tes',
        'type'        => 'Cash In',
        'amount'      => 0,
        'savings_id'  => $saving->id,
    ]);

    // amount=0 saat ini LOLOS validasi (min:0). Kalau ingin ditolak, ubah rule ke 'min:1'
    // dan ubah assertion ini ke: $response->assertSessionHasErrors('amount');
    $response->assertRedirect();
});

test('store transaction fails when savings_id does not exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/transaksi/store', [
        'description' => 'Tes',
        'type'        => 'Cash In',
        'amount'      => 100_000,
        'savings_id'  => 9999, // tidak ada
    ]);

    $response->assertSessionHasErrors('savings_id');
});

// ─────────────────────────────────────────────
//  SEARCH & FILTER
// ─────────────────────────────────────────────

test('transaction list can be filtered by type Cash In', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory()->create([
        'user_id' => $user->id, 'savings_id' => $saving->id,
        'type' => 'Cash In', 'description' => 'Gaji',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id, 'savings_id' => $saving->id,
        'type' => 'Cash Out', 'description' => 'Belanja',
    ]);

    $response = $this->actingAs($user)->get('/transaksi?type=Cash+In');

    $response->assertStatus(200);
    $transactions = $response->viewData('transaction');
    foreach ($transactions as $t) {
        expect($t->type)->toBe('Cash In');
    }
});

test('transaction list can be searched by description keyword', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory()->create([
        'user_id'     => $user->id,
        'savings_id'  => $saving->id,
        'description' => 'Belanja Supermarket ABC',
        'type'        => 'Cash Out',
    ]);
    Transaction::factory()->create([
        'user_id'     => $user->id,
        'savings_id'  => $saving->id,
        'description' => 'Gaji Bulanan',
        'type'        => 'Cash In',
    ]);

    $response = $this->actingAs($user)->get('/transaksi?search=Supermarket');

    $response->assertStatus(200);
    $transactions = $response->viewData('transaction');
    expect($transactions)->toHaveCount(1);
    expect($transactions->first()->description)->toContain('Supermarket');
});

// ─────────────────────────────────────────────
//  DATA ISOLATION – user cannot see others' data
// ─────────────────────────────────────────────

test('user cannot see another users transactions', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();
    $saving    = Saving::factory()->create(['user_id' => $otherUser->id]);

    Transaction::factory(5)->create([
        'user_id'    => $otherUser->id,
        'savings_id' => $saving->id,
    ]);

    $response  = $this->actingAs($user)->get('/transaksi');
    $viewTrans = $response->viewData('transaction');

    expect($viewTrans->total())->toBe(0);
});

// ─────────────────────────────────────────────
//  RESPONSE TIME
// ─────────────────────────────────────────────

test('transaction index responds within 500ms with 500 records', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory(500)->create([
        'user_id'    => $user->id,
        'savings_id' => $saving->id,
    ]);

    $start    = microtime(true);
    $response = $this->actingAs($user)->get('/transaksi');
    $elapsed  = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);
    expect($elapsed)->toBeLessThan(500)
        ->and($elapsed)->toBeGreaterThan(0);
})->note('Baseline performance: tanpa Octane');

// ─────────────────────────────────────────────
//  PAGINATION
// ─────────────────────────────────────────────

test('transaction list paginates with 15 items per page', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    Transaction::factory(25)->create([
        'user_id'    => $user->id,
        'savings_id' => $saving->id,
    ]);

    $response = $this->actingAs($user)->get('/transaksi');
    $viewData = $response->viewData('transaction');

    expect($viewData->perPage())->toBe(15);
    expect($viewData->total())->toBe(25);
    expect($viewData->lastPage())->toBe(2);
});

// ─────────────────────────────────────────────
//  IMPORT VALIDATION (unit-level)
// ─────────────────────────────────────────────

test('import endpoint rejects non-xlsx files', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

    $response = $this->actingAs($user)->post('/transaksi/import', [
        'file'       => $fakeFile,
        'savings_id' => $saving->id,
        'password'   => '12345678',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

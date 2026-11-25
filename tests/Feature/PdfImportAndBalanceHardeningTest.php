<?php

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DuplicateTransactionHandler;
use App\Services\PdfImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('duplicate handler avoids false positives across months, opposite types, and distinct qris ref ids', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    // 1. Admin fee bulan lalu (beda bulan -> bukan duplikat)
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash Out',
        'amount' => 6_000,
        'description' => 'Biaya Admin Bulanan',
        'transaction_date' => '2026-10-31',
    ]);

    // 2. Cash In nominal sama (beda tipe -> bukan duplikat)
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash In',
        'amount' => 100_000,
        'description' => 'Top Up Gopay',
        'transaction_date' => '2026-11-20',
    ]);

    // 3. QRIS dengan referensi berbeda (template 90% sama -> bukan duplikat)
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash Out',
        'amount' => 25_000,
        'description' => 'Pembayaran QRIS Merchant Kopi QRISRNS202680193442#9360091400001',
        'transaction_date' => '2026-11-20',
    ]);

    // 4. Transfer dengan ref ID sama namun deskripsi lain -> harus terdeteksi duplikat!
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash Out',
        'amount' => 500_000,
        'description' => 'Transfer BI-Fast ke BANK MANDIRI - Budi ID#TRX99887766',
        'transaction_date' => '2026-11-20',
    ]);

    $handler = app(DuplicateTransactionHandler::class);
    $batch = $handler->detectBatch($user->id, $saving->id, [
        [
            'type' => 'Cash Out',
            'amount' => 6_000,
            'description' => 'Biaya Admin Bulanan',
            'transaction_date' => '2026-11-30',
        ],
        [
            'type' => 'Cash Out',
            'amount' => 100_000,
            'description' => 'Top Up Gopay',
            'transaction_date' => '2026-11-20',
        ],
        [
            'type' => 'Cash Out',
            'amount' => 25_000,
            'description' => 'Pembayaran QRIS Merchant Kopi QRISRNS203021891356#9360091400002',
            'transaction_date' => '2026-11-20',
        ],
        [
            'type' => 'Cash Out',
            'amount' => 500_000,
            'description' => 'Transfer Budi ID#TRX99887766',
            'transaction_date' => '2026-11-20',
        ],
    ]);

    $txs = $batch['transactions'];
    expect($txs[0]['is_duplicate'])->toBeFalse()
        ->and($txs[1]['is_duplicate'])->toBeFalse()
        ->and($txs[2]['is_duplicate'])->toBeFalse()
        ->and($txs[3]['is_duplicate'])->toBeTrue()
        ->and($txs[3]['duplicate_type'])->toBe('exact')
        ->and($txs[3]['is_selected'])->toBeFalse();
});

test('pdf import service picks chronological latest final balance for descending statements and saves twin transactions', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 100_000]);

    $service = app(PdfImportService::class);
    // Descending order (terbaru di atas, terlama di bawah)
    $rows = [
        [
            'date' => '2026-11-20',
            'description' => 'Beli Kopi Pagi',
            'type' => 'Cash Out',
            'amount' => 25_000,
            'balance' => 450_000,
        ],
        [
            'date' => '2026-11-20',
            'description' => 'Beli Kopi Pagi',
            'type' => 'Cash Out',
            'amount' => 25_000,
            'balance' => 475_000,
        ],
        [
            'date' => '2026-11-18',
            'description' => 'Gaji Masuk',
            'type' => 'Cash In',
            'amount' => 500_000,
            'balance' => 500_000,
        ],
    ];

    $normalized = $service->normalizeExtractedRows($rows, $user->id, $saving->id);
    expect((float) $normalized['final_balance'])->toBe(450_000.0)
        // Twin transactions in same batch remain selected with warning
        ->and($normalized['transactions'][1]['duplicate_type'])->toBe('batch')
        ->and($normalized['transactions'][1]['is_selected'])->toBeTrue();

    // Save both twin transactions without overwriting each other
    $saved = $service->saveImported($user, $saving->id, $normalized['transactions'], true, $normalized['final_balance'], false);
    expect($saved['count'])->toBe(3)
        ->and(Transaction::where('user_id', $user->id)->where('description', 'Beli Kopi Pagi')->count())->toBe(2)
        ->and((float) $saving->fresh()->current_balance)->toBe(450_000.0);
});

test('destroy transaction supports skip_balance_adjust and clamps negative balance protection', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 200_000]);

    $txHist = Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash Out',
        'amount' => 150_000,
        'description' => 'Histori Import',
        'transaction_date' => '2026-11-01',
    ]);

    // Delete without altering balance
    $this->actingAs($user)
        ->delete("/transaksi/{$txHist->id}", ['skip_balance_adjust' => 1])
        ->assertRedirect();

    expect((float) $saving->fresh()->current_balance)->toBe(200_000.0)
        ->and(Transaction::find($txHist->id))->toBeNull();

    // Delete Cash In larger than current balance -> clamped at 0 instead of negative
    $txBigIn = Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash In',
        'amount' => 500_000,
        'description' => 'Cash In Lama',
        'transaction_date' => '2026-11-01',
    ]);

    $this->actingAs($user)
        ->delete("/transaksi/{$txBigIn->id}")
        ->assertRedirect();

    expect((float) $saving->fresh()->current_balance)->toBe(0.0);
});

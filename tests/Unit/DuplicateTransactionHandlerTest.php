<?php

namespace Tests\Unit;

use App\Models\Bank;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DuplicateTransactionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateTransactionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected DuplicateTransactionHandler $handler;
    protected User $user;
    protected Bank $bankBca;
    protected Bank $bankMandiri;
    protected Saving $savingBca;
    protected Saving $savingMandiri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new DuplicateTransactionHandler();

        $this->user = User::factory()->create(['status' => 'approved']);

        $this->bankBca = Bank::create(['name' => 'Bank Central Asia', 'code' => 'BCA']);
        $this->bankMandiri = Bank::create(['name' => 'Bank Mandiri', 'code' => 'MANDIRI']);

        $this->savingBca = Saving::create([
            'user_id' => $this->user->id,
            'bank_id' => $this->bankBca->id,
            'name' => 'BCA Utama',
            'account_number' => '1234567890',
            'current_balance' => 5000000,
        ]);

        $this->savingMandiri = Saving::create([
            'user_id' => $this->user->id,
            'bank_id' => $this->bankMandiri->id,
            'name' => 'Mandiri Payroll',
            'account_number' => '0987654321',
            'current_balance' => 10000000,
        ]);
    }

    public function test_normalizes_description_with_different_casing_and_spaces(): void
    {
        $this->assertTrue(
            $this->handler->isDescriptionMatching('Kopi Kenangan Senayan', 'KOPI   KENANGAN  SENAYAN')
        );

        $this->assertTrue(
            $this->handler->isDescriptionMatching('TRSF E-BANKING CR BUDI', 'trsf e-banking cr budi')
        );

        $this->assertFalse(
            $this->handler->isDescriptionMatching('Beli Bensin Shell', 'Makan Siang Sederhana')
        );
    }

    public function test_matches_amount_with_float_precision(): void
    {
        $this->assertTrue($this->handler->isAmountMatching(50000.00, 50000));
        $this->assertTrue($this->handler->isAmountMatching(12500.50, 12500.504));
        $this->assertFalse($this->handler->isAmountMatching(50000.00, 50001.00));
    }

    public function test_detects_duplicate_when_name_and_amount_match_on_same_bank(): void
    {
        // Transaksi existing di BCA
        $existing = Transaction::create([
            'user_id' => $this->user->id,
            'savings_id' => $this->savingBca->id,
            'description' => 'QRIS KOPI KENANGAN',
            'amount' => 38000,
            'type' => 'Cash Out',
            'direction' => 'out',
            'transaction_date' => '2026-10-18',
        ]);

        // Cek pencarian duplikat pada bank yang sama (BCA)
        $duplicate = $this->handler->findDuplicate(
            $this->user->id,
            $this->savingBca->id,
            'qris  kopi kenangan',
            38000
        );

        $this->assertNotNull($duplicate);
        $this->assertEquals($existing->id, $duplicate->id);
    }

    public function test_does_not_detect_duplicate_if_on_different_bank(): void
    {
        // Transaksi ada di BCA
        Transaction::create([
            'user_id' => $this->user->id,
            'savings_id' => $this->savingBca->id,
            'description' => 'Beli Pulsa Telkomsel',
            'amount' => 100000,
            'type' => 'Cash Out',
            'direction' => 'out',
            'transaction_date' => '2026-10-18',
        ]);

        // Cari pada Mandiri (bank berbeda) -> bukan duplikat
        $duplicateOnMandiri = $this->handler->findDuplicate(
            $this->user->id,
            $this->savingMandiri->id,
            'Beli Pulsa Telkomsel',
            100000
        );

        $this->assertNull($duplicateOnMandiri);
    }

    public function test_does_not_detect_duplicate_if_amount_differs(): void
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'savings_id' => $this->savingBca->id,
            'description' => 'Tagihan Listrik PLN',
            'amount' => 250000,
            'type' => 'Cash Out',
            'direction' => 'out',
            'transaction_date' => '2026-10-18',
        ]);

        $duplicate = $this->handler->findDuplicate(
            $this->user->id,
            $this->savingBca->id,
            'Tagihan Listrik PLN',
            300000 // Nominal beda
        );

        $this->assertNull($duplicate);
    }

    public function test_detect_batch_marks_duplicates_and_auto_unselects(): void
    {
        // 1 transaksi sudah tercatat di database
        Transaction::create([
            'user_id' => $this->user->id,
            'savings_id' => $this->savingBca->id,
            'description' => 'Gaji Bulanan PT Maju',
            'amount' => 15000000,
            'type' => 'Cash In',
            'direction' => 'in',
            'transaction_date' => '2026-10-15',
        ]);

        $incomingBatch = [
            [
                'description' => 'gaji bulanan pt maju',
                'amount' => 15000000,
                'transaction_date' => '2026-10-15',
                'type' => 'Cash In',
                'is_selected' => true,
            ],
            [
                'description' => 'Pembelian Supermarket',
                'amount' => 350000,
                'transaction_date' => '2026-10-16',
                'type' => 'Cash Out',
                'is_selected' => true,
            ],
            // In-batch duplicate
            [
                'description' => 'Pembelian Supermarket',
                'amount' => 350000,
                'transaction_date' => '2026-10-16',
                'type' => 'Cash Out',
                'is_selected' => true,
            ],
        ];

        $result = $this->handler->detectBatch($this->user->id, $this->savingBca->id, $incomingBatch);

        $this->assertEquals(2, $result['duplicate_count']);
        $this->assertEquals(1, $result['new_count']);

        $processed = $result['transactions'];

        // Item 0: duplikat dari DB
        $this->assertTrue($processed[0]['is_duplicate']);
        $this->assertFalse($processed[0]['is_selected'], 'Transaksi duplikat otomatis di-unselect');
        $this->assertEquals('exact', $processed[0]['duplicate_type']);
        $this->assertNotNull($processed[0]['matched_transaction']);

        // Item 1: baru
        $this->assertFalse($processed[1]['is_duplicate']);
        $this->assertTrue($processed[1]['is_selected']);

        // Item 2: duplikat kembar di dalam file batch
        $this->assertTrue($processed[2]['is_duplicate']);
        $this->assertEquals('batch', $processed[2]['duplicate_type']);
        $this->assertFalse($processed[2]['is_selected']);
    }
}


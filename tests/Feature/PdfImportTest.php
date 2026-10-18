<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use App\Services\PdfImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PdfImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Bank $bank;
    protected Saving $saving;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'status' => 'approved',
        ]);

        $this->bank = Bank::create([
            'name' => 'Bank Jago',
            'code' => 'JAGO',
        ]);

        $this->saving = Saving::create([
            'user_id' => $this->user->id,
            'bank_id' => $this->bank->id,
            'name' => 'Kantong Utama',
            'account_number' => '1029384756',
            'current_balance' => 1000000,
        ]);

        $this->category = Category::create([
            'name' => 'Transportasi',
            'type' => 'Cash Out',
            'color' => 'blue',
            'icon' => 'fa-car',
        ]);
    }

    public function test_amount_parser_handles_indonesian_number_formats()
    {
        $service = app(PdfImportService::class);

        $this->assertEquals(15000.0, $service->parseIndonesianAmount('15.000'));
        $this->assertEquals(15000.0, $service->parseIndonesianAmount('-15.000'));
        $this->assertEquals(4955000.0, $service->parseIndonesianAmount('+4.955.000,00'));
        $this->assertEquals(4358.73, $service->parseIndonesianAmount('4.358,73'));
        $this->assertEquals(50000.0, $service->parseIndonesianAmount('Rp 50.000'));
    }

    public function test_date_parser_handles_indonesian_month_names()
    {
        $service = app(PdfImportService::class);

        $date1 = $service->parseIndonesianDate('25 Sep 2026 15:59');
        $this->assertEquals('2026-09-25', $date1->format('Y-m-d'));

        $date2 = $service->parseIndonesianDate('18 Oktober 2026');
        $this->assertEquals('2026-10-18', $date2->format('Y-m-d'));

        $date3 = $service->parseIndonesianDate('02 Jan 2026');
        $this->assertEquals('2026-01-02', $date3->format('Y-m-d'));
    }

    public function test_normalize_extracted_rows_properly_structures_jago_bank_format()
    {
        $service = app(PdfImportService::class);

        $sampleRows = [
            [
                'Date & Time' => '25 Sep 2026 15:59',
                'Source/Destination' => 'Bensin Movement between Pockets',
                'Transaction Details' => 'Pocket Money In ID# 260925-WKFC-8Z9JB6',
                'Notes' => '',
                'Amount' => '-15.000',
                'Balance' => '4.358,73',
            ],
            [
                'Date & Time' => '26 Sep 2026 10:00',
                'Source/Destination' => 'Gaji Bulanan',
                'Transaction Details' => 'Transfer Payroll PT ABC',
                'Notes' => '',
                'Amount' => '+4.955.000,00',
                'Balance' => '4.959.358,73',
            ]
        ];

        $result = $service->normalizeExtractedRows($sampleRows, $this->user->id);

        $this->assertEquals(2, $result['total_items']);
        $this->assertEquals(4955000.0, $result['total_cash_in']);
        $this->assertEquals(15000.0, $result['total_cash_out']);
        $this->assertEquals(4959358.73, $result['final_balance']);

        $item1 = $result['transactions'][0];
        $this->assertEquals('Cash Out', $item1['type']);
        $this->assertEquals(15000.0, $item1['amount']);
        $this->assertEquals('2026-09-25', $item1['transaction_date']);
        $this->assertStringContainsString('Bensin', $item1['description']);

        $item2 = $result['transactions'][1];
        $this->assertEquals('Cash In', $item2['type']);
        $this->assertEquals(4955000.0, $item2['amount']);
    }

    public function test_save_imported_creates_transactions_and_updates_balance()
    {
        $service = app(PdfImportService::class);

        $transactionsToSave = [
            [
                'transaction_date' => '2026-10-18',
                'type' => 'Cash Out',
                'description' => 'Beli Bensin Shell',
                'amount' => 50000,
                'category_id' => $this->category->id,
                'is_selected' => true,
            ],
            [
                'transaction_date' => '2026-10-18',
                'type' => 'Cash In',
                'description' => 'Transfer Masuk dari Teman',
                'amount' => 150000,
                'category_id' => null,
                'is_selected' => true,
            ],
            [
                'transaction_date' => '2026-10-18',
                'type' => 'Cash Out',
                'description' => 'Transaksi Tidak Dipilih',
                'amount' => 20000,
                'is_selected' => false,
            ]
        ];

        $result = $service->saveImported(
            $this->user,
            $this->saving->id,
            $transactionsToSave,
            true, // update balance
            1100000 // final balance
        );

        $this->assertEquals(2, $result['count']);

        // Check transactions in DB
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'savings_id' => $this->saving->id,
            'description' => 'Beli Bensin Shell',
            'amount' => 50000,
            'type' => 'Cash Out',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'savings_id' => $this->saving->id,
            'description' => 'Transfer Masuk dari Teman',
            'amount' => 150000,
            'type' => 'Cash In',
        ]);

        // Unselected transaction should NOT be in DB
        $this->assertDatabaseMissing('transactions', [
            'description' => 'Transaksi Tidak Dipilih',
        ]);

        // Verify balance updated
        $this->saving->refresh();
        $this->assertEquals(1100000, $this->saving->current_balance);
    }

    public function test_web_import_pdf_save_endpoint()
    {
        $response = $this->actingAs($this->user)->postJson(route('transaction.import_pdf_save'), [
            'savings_id' => $this->saving->id,
            'update_balance' => true,
            'final_balance' => 2500000,
            'transactions' => [
                [
                    'transaction_date' => '2026-10-18',
                    'type' => 'Cash In',
                    'description' => 'Bonus Project',
                    'amount' => 1500000,
                    'is_selected' => true,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'count' => 1,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Bonus Project',
            'amount' => 1500000,
            'type' => 'Cash In',
        ]);

        $this->saving->refresh();
        $this->assertEquals(2500000, $this->saving->current_balance);
    }

    public function test_api_import_pdf_save_endpoint()
    {
        $token = $this->user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/transactions/import-pdf/save', [
                'savings_id' => $this->saving->id,
                'update_balance' => false,
                'transactions' => [
                    [
                        'transaction_date' => '2026-10-18',
                        'type' => 'Cash Out',
                        'description' => 'Belanja Supermarket API',
                        'amount' => 85000,
                        'is_selected' => true,
                    ]
                ]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'count' => 1,
            ]
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Belanja Supermarket API',
            'amount' => 85000,
        ]);
    }

    public function test_dashboard_displays_import_transaksi_button()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Import Transaksi');
        $response->assertSee('openPdfImportModal');
        $response->assertSee('pdfImportModal');
        $response->assertSee('pdfImportReviewModal');
        $response->assertSee('pdfImportSuccessModal');
    }
}

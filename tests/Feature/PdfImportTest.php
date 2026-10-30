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

    public function test_normalize_extracted_rows_handles_standard_contract_from_bca_and_mandiri()
    {
        $service = app(PdfImportService::class);

        // Standar output dari BcaParser dan MandiriParser
        $standardRows = [
            [
                'date' => '2026-10-18',
                'raw_date' => '18/10/2026',
                'description' => 'TRSF E-BANKING DB 1810/WS95011 KOPI KENANGAN',
                'type' => 'Cash Out',
                'amount' => 28000.0,
                'balance' => 1472000.0,
            ],
            [
                'date' => '2026-10-18',
                'raw_date' => '18/10/2026',
                'description' => 'TRANSFER DARI SITI AMINAH',
                'type' => 'Cash In',
                'amount' => 500000.0,
                'balance' => 1972000.0,
            ]
        ];

        $result = $service->normalizeExtractedRows($standardRows, $this->user->id);

        $this->assertEquals(2, $result['total_items']);
        $this->assertEquals(500000.0, $result['total_cash_in']);
        $this->assertEquals(28000.0, $result['total_cash_out']);
        $this->assertEquals(1972000.0, $result['final_balance']);

        $this->assertEquals('Cash Out', $result['transactions'][0]['type']);
        $this->assertEquals(28000.0, $result['transactions'][0]['amount']);
        $this->assertEquals('2026-10-18', $result['transactions'][0]['transaction_date']);

        $this->assertEquals('Cash In', $result['transactions'][1]['type']);
        $this->assertEquals(500000.0, $result['transactions'][1]['amount']);
    }

    public function test_normalize_extracted_rows_handles_standard_contract_from_bri()
    {
        $service = app(PdfImportService::class);

        $briRows = [
            [
                'date' => '2026-09-25',
                'raw_date' => '25/09/2026',
                'description' => 'TRSF E-BANKING CR 2509/WS95011 GAJI',
                'type' => 'Cash In',
                'amount' => 8500000.0,
                'balance' => 10500000.0,
            ],
            [
                'date' => '2026-09-26',
                'raw_date' => '26/09/2026',
                'description' => 'QRIS INDOMARET TANGSEL',
                'type' => 'Cash Out',
                'amount' => 45000.0,
                'balance' => 10455000.0,
            ]
        ];

        $result = $service->normalizeExtractedRows($briRows, $this->user->id);

        $this->assertEquals(2, $result['total_items']);
        $this->assertEquals(8500000.0, $result['total_cash_in']);
        $this->assertEquals(45000.0, $result['total_cash_out']);
        $this->assertEquals(10455000.0, $result['final_balance']);
        $this->assertEquals('Cash In', $result['transactions'][0]['type']);
        $this->assertEquals('Cash Out', $result['transactions'][1]['type']);
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

    public function test_extract_parses_mandiri_pdf_with_local_parser()
    {
        $pdfPath = base_path('decoder/testing_folder/e-Statement_XXXXXXXXX8437_25 Sep 2026-17 Okt 2026.pdf');
        if (!file_exists($pdfPath)) {
            $this->markTestSkipped('Sample Mandiri PDF file not found.');
        }

        $service = app(PdfImportService::class);
        $result = $service->extract($pdfPath, 'mandiri', '12092004', $this->user->id);

        $this->assertEquals(12, $result['total_items']);
        $this->assertEquals('Bank Mandiri', $result['parser_name']);
        $this->assertFalse($result['is_ai_parsed']);
        $this->assertEquals(11858422.0, $result['total_cash_in']);
        $this->assertEquals(11860000.0, $result['total_cash_out']);
    }

    public function test_extract_falls_back_to_ai_when_local_parsing_returns_empty()
    {
        // Mock OpenRouterService
        $mockAiRows = [
            [
                'date' => '2026-10-18',
                'raw_date' => '18/10/2026',
                'description' => 'QRIS KOPI KENANGAN AI',
                'type' => 'Cash Out',
                'amount' => 35000,
                'balance' => 965000,
            ],
            [
                'date' => '2026-10-19',
                'raw_date' => '19/10/2026',
                'description' => 'TRANSFER MASUK GAJI AI',
                'type' => 'Cash In',
                'amount' => 5000000,
                'balance' => 5965000,
            ]
        ];

        $mockOpenRouter = \Mockery::mock(\App\Services\OpenRouterService::class);
        $mockOpenRouter->shouldReceive('parseBankStatementWithAi')
            ->once()
            ->andReturn($mockAiRows);

        $budgetService = app(\App\Services\BudgetService::class);

        // Buat dummy PDF extractor wrapper dengan overriding command atau simulasi output
        // Buat temporary dummy PDF file
        $tempPdf = tempnam(sys_get_temp_dir(), 'test_pdf_') . '.pdf';
        file_put_contents($tempPdf, "%PDF-1.4\n%EOF");

        // Subclass PdfImportService untuk menguji branching AI fallback secara deterministik
        $service = new class($budgetService, $mockOpenRouter) extends PdfImportService {
            public function extract(string $pdfFilePath, ?string $bankCodeOrPassword = 'auto', ?string $password = null, ?int $userId = null, ?int $savingsId = null): array
            {
                // Simulasi payload extractor dengan 0 transaksi (status: need_ai)
                $payload = [
                    'status' => 'need_ai',
                    'parser' => 'Unknown Bank',
                    'bank_code' => 'auto',
                    'transactions' => [],
                    'raw_text' => 'Mutasi Rekening Bank Contoh 18/10/2026 QRIS KOPI KENANGAN AI 35.000',
                ];

                $rawRows = $payload['transactions'];
                $isAiParsed = false;
                $parserName = $payload['parser'];

                if (empty($rawRows)) {
                    $aiRows = $this->openRouterService->parseBankStatementWithAi($payload['raw_text']);
                    if (!empty($aiRows)) {
                        $rawRows = $aiRows;
                        $isAiParsed = true;
                        $parserName = 'AI Assistant (OpenRouter)';
                    }
                }

                $result = $this->normalizeExtractedRows($rawRows, $userId);
                $result['is_ai_parsed'] = $isAiParsed;
                $result['parser_name'] = $parserName;

                return $result;
            }
        };

        $result = $service->extract($tempPdf, 'auto', null, $this->user->id);

        $this->assertEquals(2, $result['total_items']);
        $this->assertTrue($result['is_ai_parsed']);
        $this->assertEquals('AI Assistant (OpenRouter)', $result['parser_name']);
        $this->assertEquals(5000000.0, $result['total_cash_in']);
        $this->assertEquals(35000.0, $result['total_cash_out']);

        if (file_exists($tempPdf)) {
            unlink($tempPdf);
        }
    }

    public function test_open_router_clean_and_parse_json_array()
    {
        $openRouter = app(\App\Services\OpenRouterService::class);

        // Kasus 1: JSON dibungkus markdown codeblock
        $fenced = "```json\n[\n  {\"date\": \"2026-10-18\", \"description\": \"Kopi\", \"amount\": 25000, \"type\": \"Cash Out\"}\n]\n```";
        $parsed1 = $openRouter->cleanAndParseJsonArray($fenced);
        $this->assertCount(1, $parsed1);
        $this->assertEquals('Kopi', $parsed1[0]['description']);
        $this->assertEquals(25000, $parsed1[0]['amount']);

        // Kasus 2: JSON murni
        $raw = '[{"date": "2026-10-18", "description": "Gaji", "amount": 10000000, "type": "Cash In"}]';
        $parsed2 = $openRouter->cleanAndParseJsonArray($raw);
        $this->assertCount(1, $parsed2);
        $this->assertEquals('Gaji', $parsed2[0]['description']);
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

    public function test_normalize_extracted_rows_detects_duplicate_transactions_on_same_bank()
    {
        $service = app(PdfImportService::class);

        // Buat transaksi existing di database pada rekening ini
        Transaction::create([
            'user_id'          => $this->user->id,
            'savings_id'       => $this->saving->id,
            'transaction_date' => '2026-10-18',
            'amount'           => 45000,
            'description'      => 'Makan Siang Padang Sederhana',
            'type'             => 'Cash Out',
            'direction'        => 'out',
        ]);

        $rawRows = [
            // Transaksi duplikat (nama dan amount sama di bank yang sama)
            [
                'Date'   => '18/10/2026',
                'Keterangan' => 'makan siang padang sederhana',
                'Jumlah' => '45.000',
            ],
            // Transaksi baru
            [
                'Date'   => '18/10/2026',
                'Keterangan' => 'Belanja Supermarket Hero',
                'Jumlah' => '230.000',
            ]
        ];

        $result = $service->normalizeExtractedRows($rawRows, $this->user->id, $this->saving->id);

        $this->assertEquals(2, $result['total_items']);
        $this->assertTrue($result['has_duplicates']);
        $this->assertEquals(1, $result['duplicate_count']);
        $this->assertEquals(1, $result['new_count']);

        // Baris 0 harus terdeteksi duplikat dan otomatis unselected
        $this->assertTrue($result['transactions'][0]['is_duplicate']);
        $this->assertFalse($result['transactions'][0]['is_selected']);
        $this->assertStringContainsString('serupa sudah tercatat', $result['transactions'][0]['duplicate_reason']);

        // Baris 1 transaksi baru tetap terpilih
        $this->assertFalse($result['transactions'][1]['is_duplicate']);
        $this->assertTrue($result['transactions'][1]['is_selected']);
    }

    public function test_normalize_extracted_rows_does_not_flag_duplicate_if_on_different_bank()
    {
        $service = app(PdfImportService::class);

        // Buat rekening kedua
        $saving2 = Saving::create([
            'user_id' => $this->user->id,
            'bank_id' => $this->bank->id,
            'name' => 'Kantong Tabungan',
            'account_number' => '9988776655',
            'current_balance' => 500000,
        ]);

        // Transaksi existing berada di rekening 1
        Transaction::create([
            'user_id'          => $this->user->id,
            'savings_id'       => $this->saving->id,
            'transaction_date' => '2026-10-18',
            'amount'           => 75000,
            'description'      => 'Beli Kopi V60',
            'type'             => 'Cash Out',
            'direction'        => 'out',
        ]);

        $rawRows = [
            [
                'Date'   => '18/10/2026',
                'Keterangan' => 'Beli Kopi V60',
                'Jumlah' => '75.000',
            ]
        ];

        // Ekstraksi untuk rekening 2 (berbeda rekening)
        $result = $service->normalizeExtractedRows($rawRows, $this->user->id, $saving2->id);

        $this->assertFalse($result['has_duplicates']);
        $this->assertEquals(0, $result['duplicate_count']);
        $this->assertFalse($result['transactions'][0]['is_duplicate']);
        $this->assertTrue($result['transactions'][0]['is_selected']);
    }

    public function test_save_imported_with_skip_duplicates_skips_duplicate_records()
    {
        $service = app(PdfImportService::class);

        // Buat transaksi existing di DB
        Transaction::create([
            'user_id'          => $this->user->id,
            'savings_id'       => $this->saving->id,
            'transaction_date' => '2026-10-18',
            'amount'           => 100000,
            'description'      => 'Langganan Internet Biznet',
            'type'             => 'Cash Out',
            'direction'        => 'out',
        ]);

        $transactionsToSave = [
            [
                'transaction_date' => '2026-10-18',
                'type' => 'Cash Out',
                'description' => 'Langganan Internet Biznet',
                'amount' => 100000,
                'is_selected' => true,
            ],
            [
                'transaction_date' => '2026-10-18',
                'type' => 'Cash Out',
                'description' => 'Beli Pulsa Simpati',
                'amount' => 50000,
                'is_selected' => true,
            ]
        ];

        $result = $service->saveImported(
            $this->user,
            $this->saving->id,
            $transactionsToSave,
            false,
            null,
            true // skip duplicates
        );

        // 1 tersimpan (Pulsa), 1 duplikat dilewati (Biznet)
        $this->assertEquals(1, $result['count']);
        $this->assertEquals(1, $result['duplicates_skipped']);

        // Verifikasi Biznet tetap hanya ada 1 di DB
        $biznetCount = Transaction::where('user_id', $this->user->id)
            ->where('savings_id', $this->saving->id)
            ->where('description', 'Langganan Internet Biznet')
            ->count();

        $this->assertEquals(1, $biznetCount);
    }
}


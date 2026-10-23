<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class PdfImportService
{
    protected BudgetService $budgetService;
    protected OpenRouterService $openRouterService;
    protected DuplicateTransactionHandler $duplicateHandler;

    public function __construct(
        BudgetService $budgetService,
        OpenRouterService $openRouterService,
        ?DuplicateTransactionHandler $duplicateHandler = null
    ) {
        $this->budgetService = $budgetService;
        $this->openRouterService = $openRouterService;
        $this->duplicateHandler = $duplicateHandler ?? app(DuplicateTransactionHandler::class);
    }

    /**
     * Cari path interpreter Python yang valid.
     */
    public function getPythonBinary(): string
    {
        $configured = config('services.python_path') ?: env('PYTHON_PATH');
        if ($configured && (file_exists($configured) || is_executable($configured))) {
            return $configured;
        }

        $venvPython = base_path('decoder/.venv/bin/python3');
        if (file_exists($venvPython) && is_executable($venvPython)) {
            return $venvPython;
        }

        $venvPythonAlt = base_path('decoder/.venv/bin/python');
        if (file_exists($venvPythonAlt) && is_executable($venvPythonAlt)) {
            return $venvPythonAlt;
        }

        $candidates = [
            '/opt/homebrew/bin/python3.12',
            '/opt/homebrew/bin/python3',
            '/usr/local/bin/python3',
            '/usr/bin/python3',
            'python3'
        ];

        foreach ($candidates as $cand) {
            if ($cand === 'python3' || (file_exists($cand) && is_executable($cand))) {
                return $cand;
            }
        }

        return 'python3';
    }

    /**
     * Ekstrak tabel transaksi dari file PDF menggunakan script Python multi-bank.
     *
     * @param string $pdfFilePath Path absolut ke file PDF
     * @param string|null $bankCodeOrPassword Kode bank (jago/bca/mandiri) atau password jika legacy
     * @param string|null $password Password PDF jika terenkripsi
     * @param int|null $userId ID User untuk auto-assign kategori & budget item
     * @param int|null $savingsId ID Rekening untuk deteksi duplikat transaksi
     * @return array
     */
    public function extract(string $pdfFilePath, ?string $bankCodeOrPassword = 'auto', ?string $password = null, ?int $userId = null, ?int $savingsId = null): array
    {
        if (!file_exists($pdfFilePath)) {
            throw new InvalidArgumentException("File PDF tidak ditemukan pada path: {$pdfFilePath}");
        }

        $pythonBin = $this->getPythonBinary();
        $scriptPath = base_path('decoder/pdf_extractor.py');

        if (!file_exists($scriptPath)) {
            throw new RuntimeException("Script ekstraktor PDF tidak ditemukan di: {$scriptPath}");
        }

        // Tangani parameter fleksibel untuk backward compatibility
        $knownBankCodes = ['jago', 'bca', 'mandiri', 'bri', 'bni', 'cimb', 'seabank', 'auto', '014', '008', '213', '002', '009'];
        $bankCode = 'auto';
        $actualPassword = $password;

        if ($bankCodeOrPassword && in_array(strtolower($bankCodeOrPassword), $knownBankCodes)) {
            $bankCode = strtolower($bankCodeOrPassword);
        } elseif ($bankCodeOrPassword && $password === null) {
            // Pemanggilan legacy: extract($path, $password, $userId)
            $actualPassword = $bankCodeOrPassword;
            $bankCode = 'auto';
        }

        $escapedPdf = escapeshellarg($pdfFilePath);
        $escapedBank = escapeshellarg($bankCode);
        $escapedPassword = $actualPassword ? escapeshellarg($actualPassword) : '';

        $sitePackages = base_path('decoder/.venv/lib/python3.12/site-packages');
        $pythonEnv = file_exists($sitePackages) ? 'PYTHONPATH=' . escapeshellarg($sitePackages) . ' ' : '';
        $command = "{$pythonEnv}{$pythonBin} " . escapeshellarg($scriptPath) . " {$escapedPdf} {$escapedBank} {$escapedPassword} 2>&1";

        Log::info('Menjalankan ekstraktor PDF multi-bank', [
            'command' => $command,
            'bank_code' => $bankCode,
            'has_password' => !empty($actualPassword),
        ]);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        $outputText = implode("\n", $output);

        if ($returnCode !== 0) {
            Log::error('Ekstraktor PDF gagal', [
                'return_code' => $returnCode,
                'output' => $outputText,
            ]);

            if (str_contains(strtolower($outputText), 'password salah') || str_contains(strtolower($outputText), 'terproteksi password') || str_contains(strtolower($outputText), 'membutuhkan password')) {
                throw new InvalidArgumentException('Password PDF salah atau dokumen terproteksi password.');
            }

            if (!str_contains($outputText, '===JSON_START===')) {
                throw new RuntimeException("Gagal mengekstrak file PDF: " . substr($outputText, 0, 300));
            }
        }

        // Cari JSON di antara marker ===JSON_START=== dan ===JSON_END===
        $jsonString = '';
        if (preg_match('/===JSON_START===\s*(.*?)\s*===JSON_END===/s', $outputText, $matches)) {
            $jsonString = trim($matches[1]);
        } else {
            // Fallback: cari blok JSON array atau object pertama
            if (preg_match('/(\[\s*\{.*\}\s*\]|\{\s*".*"\s*:.*?\})/s', $outputText, $matches)) {
                $jsonString = trim($matches[1]);
            }
        }

        $payload = !empty($jsonString) ? json_decode($jsonString, true) : null;
        $rawRows = [];
        $rawText = '';
        $parserName = 'Local Parser';
        $isAiParsed = false;

        if (is_array($payload)) {
            if (isset($payload['transactions']) && is_array($payload['transactions'])) {
                $rawRows = $payload['transactions'];
                $rawText = $payload['raw_text'] ?? '';
                $parserName = $payload['parser'] ?? 'Local Parser';
            } else {
                // Format flat array legacy
                $rawRows = $payload;
            }
        }

        // EVALUASI: Sesuai atau Tidak?
        // Sesuai jika terdapat minimal 1 transaksi valid hasil parser lokal
        $isSuitable = !empty($rawRows) && is_array($rawRows) && count($rawRows) > 0;

        if (!$isSuitable) {
            Log::info('Hasil parsing lokal PDF kosong atau tidak sesuai. Mencoba AI fallback...', [
                'bank_code' => $bankCode,
                'parser' => $parserName,
                'has_raw_text' => !empty($rawText),
            ]);

            if (empty($rawText)) {
                $rawText = $outputText;
            }

            if (!empty($rawText)) {
                try {
                    $aiRows = $this->openRouterService->parseBankStatementWithAi($rawText);
                    if (!empty($aiRows) && is_array($aiRows) && count($aiRows) > 0) {
                        $rawRows = $aiRows;
                        $isAiParsed = true;
                        $parserName = 'AI Assistant (OpenRouter)';
                        Log::info('Berhasil mengekstrak transaksi via AI fallback', ['count' => count($rawRows)]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('AI bank statement parsing failed: ' . $e->getMessage());
                }
            }
        }

        if (empty($rawRows)) {
            Log::error('Tidak ditemukan transaksi dari PDF extractor maupun AI fallback', ['output' => substr($outputText, 0, 500)]);
            throw new RuntimeException('Tidak ada data transaksi yang dapat dibaca dari file PDF ini.');
        }

        $result = $this->normalizeExtractedRows($rawRows, $userId, $savingsId);
        $result['is_ai_parsed'] = $isAiParsed;
        $result['parser_name'] = $parserName;

        return $result;
    }


    /**
     * Normalisasi baris transaksi dari berbagai format tabel e-statement.
     */
    public function normalizeExtractedRows(array $rawRows, ?int $userId = null, ?int $savingsId = null): array
    {
        $items = [];
        $totalCashIn = 0;
        $totalCashOut = 0;
        $finalBalance = null;

        // Ambil budget aktif user jika userId tersedia untuk auto-matching
        $activeBudgets = collect([]);
        if ($userId) {
            $budget = Budget::with('items.category')
                ->where('user_id', $userId)
                ->where('month', (int) now()->month)
                ->where('year', (int) now()->year)
                ->first();
            if ($budget) {
                $activeBudgets = $budget->items;
            }
        }

        foreach ($rawRows as $index => $row) {
            // 1. Ekstrak Tanggal (Mendukung standar 'date' atau format mentah tabel)
            $dateRaw = $row['date'] ?? $row['raw_date'] ?? $row['Date & Time'] ?? $row['Date'] ?? $row['Tanggal & Jam'] ?? $row['Tanggal'] ?? $row['Waktu'] ?? '';
            $parsedDate = $this->parseIndonesianDate($dateRaw);

            // 2. Ekstrak Keterangan / Deskripsi
            if (!empty($row['description'])) {
                $description = trim($row['description']);
            } else {
                $descParts = [];
                if (!empty($row['Source/Destination'])) {
                    $descParts[] = trim($row['Source/Destination']);
                }
                if (!empty($row['Transaction Details'])) {
                    $descParts[] = trim($row['Transaction Details']);
                }
                if (!empty($row['Notes'])) {
                    $descParts[] = trim($row['Notes']);
                }
                if (empty($descParts)) {
                    $descParts[] = $row['Keterangan'] ?? $row['Deskripsi'] ?? $row['Uraian'] ?? 'Transaksi Bank';
                }
                $description = implode(' - ', array_filter($descParts));
            }
            $description = preg_replace('/\s+/', ' ', $description);

            // 3. Ekstrak Nominal & Tipe
            if (isset($row['amount']) && is_numeric($row['amount']) && isset($row['type'])) {
                $type = in_array($row['type'], ['Cash In', 'Cash Out', 'Transfer', 'Tabungan']) ? $row['type'] : 'Cash Out';
                $amount = abs((float) $row['amount']);
            } else {
                $amountRaw = $row['Amount'] ?? $row['Jumlah'] ?? $row['Nominal'] ?? '';
                $type = 'Cash Out';

                if (str_starts_with(trim($amountRaw), '+')) {
                    $type = 'Cash In';
                } elseif (str_starts_with(trim($amountRaw), '-')) {
                    $type = 'Cash Out';
                } elseif (!empty($row['Dana Masuk']) && $this->parseIndonesianAmount($row['Dana Masuk']) > 0) {
                    $type = 'Cash In';
                    $amountRaw = $row['Dana Masuk'];
                } elseif (!empty($row['Dana Keluar']) && $this->parseIndonesianAmount($row['Dana Keluar']) > 0) {
                    $type = 'Cash Out';
                    $amountRaw = $row['Dana Keluar'];
                }

                $amount = $this->parseIndonesianAmount($amountRaw);
            }

            if ($amount <= 0) {
                // Abaikan baris tanpa pergerakan uang
                continue;
            }

            // 4. Ekstrak Saldo (Balance)
            if (isset($row['balance']) && is_numeric($row['balance'])) {
                $balance = (float) $row['balance'];
            } else {
                $balanceRaw = $row['Balance'] ?? $row['Saldo'] ?? '';
                $balance = !empty($balanceRaw) ? $this->parseIndonesianAmount($balanceRaw) : null;
            }
            if ($balance !== null) {
                $finalBalance = $balance;
            }

            // 5. Auto-resolve Category & Budget Item jika memungkinkan
            $suggestedCategoryId = null;
            $suggestedCategoryName = null;
            $suggestedBudgetItemId = null;

            if ($userId) {
                try {
                    $category = $this->budgetService->resolveCategory($description, $userId);
                    if ($category) {
                        $suggestedCategoryId = $category->id;
                        $suggestedCategoryName = $category->name;

                        // Cari budget item yang cocok dengan kategori ini
                        $matchingBudgetItem = $activeBudgets->firstWhere('category_id', $category->id);
                        if ($matchingBudgetItem) {
                            $suggestedBudgetItemId = $matchingBudgetItem->id;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore category resolution failure
                }
            }

            if ($type === 'Cash In') {
                $totalCashIn += $amount;
            } else {
                $totalCashOut += $amount;
            }

            $items[] = [
                'index'                  => $index,
                'raw_date'               => $dateRaw,
                'transaction_date'       => $parsedDate->format('Y-m-d'),
                'transaction_date_label' => $parsedDate->translatedFormat('d M Y'),
                'type'                   => $type,
                'description'            => $description,
                'amount'                 => $amount,
                'amount_formatted'       => 'Rp ' . number_format($amount, 0, ',', '.'),
                'balance'                => $balance,
                'balance_formatted'      => $balance !== null ? 'Rp ' . number_format($balance, 0, ',', '.') : null,
                'category_id'            => $suggestedCategoryId,
                'category_name'          => $suggestedCategoryName,
                'budget_item_id'         => $suggestedBudgetItemId,
                'is_selected'            => true,
            ];
        }

        $duplicateCount = 0;
        $newCount = count($items);

        if ($userId && $savingsId) {
            $batchResult = $this->duplicateHandler->detectBatch($userId, $savingsId, $items);
            $items = $batchResult['transactions'];
            $duplicateCount = $batchResult['duplicate_count'];
            $newCount = $batchResult['new_count'];
        }

        return [
            'total_items'       => count($items),
            'duplicate_count'   => $duplicateCount,
            'new_count'         => $newCount,
            'has_duplicates'    => $duplicateCount > 0,
            'total_cash_in'     => $totalCashIn,
            'total_cash_in_fmt' => 'Rp ' . number_format($totalCashIn, 0, ',', '.'),
            'total_cash_out'    => $totalCashOut,
            'total_cash_out_fmt'=> 'Rp ' . number_format($totalCashOut, 0, ',', '.'),
            'final_balance'     => $finalBalance,
            'final_balance_fmt' => $finalBalance !== null ? 'Rp ' . number_format($finalBalance, 0, ',', '.') : null,
            'transactions'      => $items,
        ];
    }

    /**
     * Konversi format nominal Indonesia (15.000, 4.955.000,00) menjadi float murni.
     */
    public function parseIndonesianAmount(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return abs((float) $value);
        }

        $str = (string) $value;
        $str = preg_replace('/[^\d.,]/', '', $str);

        if ($str === '') {
            return 0.0;
        }

        // Jika terdapat titik dan koma (cth: 1.234.567,89)
        if (str_contains($str, '.') && str_contains($str, ',')) {
            $lastDot = strrpos($str, '.');
            $lastComma = strrpos($str, ',');
            if ($lastComma > $lastDot) {
                // Standar Indonesia: titik ribuan, koma desimal
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // Standar US: koma ribuan, titik desimal
                $str = str_replace(',', '', $str);
            }
        } elseif (str_contains($str, '.')) {
            // Hanya ada titik
            $parts = explode('.', $str);
            $lastPart = end($parts);
            if (strlen($lastPart) === 3) {
                // Format ribuan tanpa desimal: 15.000 -> 15000
                $str = str_replace('.', '', $str);
            }
            // Jika desimal biasa seperti 15.5, biarkan tetap ada titik
        } elseif (str_contains($str, ',')) {
            // Hanya ada koma, ganti jadi titik desimal
            $str = str_replace(',', '.', $str);
        }

        return abs((float) $str);
    }

    /**
     * Parsing tanggal e-statement Indonesia/Inggris ke Carbon.
     */
    public function parseIndonesianDate(string $rawDate): Carbon
    {
        $clean = strip_tags($rawDate);
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        // Format DD/MM/YYYY atau DD/MM/YY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/', $clean, $m)) {
            $year = strlen($m[3]) === 2 ? (int) ('20' . $m[3]) : (int) $m[3];
            return Carbon::createFromDate($year, (int) $m[2], (int) $m[1])->startOfDay();
        }

        // Format YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $clean, $m)) {
            return Carbon::createFromDate((int) $m[1], (int) $m[2], (int) $m[3])->startOfDay();
        }

        // Terjemahkan nama bulan Indonesia ke Inggris untuk Carbon
        $monthTranslations = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'agu' => 'Aug',
            'ags' => 'Aug', 'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov',
            'des' => 'Dec',
            'januari' => 'January', 'februari' => 'February', 'maret' => 'March',
            'april' => 'April', 'juni' => 'June', 'juli' => 'July',
            'agustus' => 'August', 'september' => 'September', 'oktober' => 'October',
            'november' => 'November', 'desember' => 'December',
        ];

        foreach ($monthTranslations as $id => $en) {
            $clean = preg_replace('/\b' . preg_quote($id, '/') . '\b/i', $en, $clean);
        }

        try {
            return Carbon::parse($clean);
        } catch (\Exception $e) {
            return now();
        }
    }

    /**
     * Simpan kumpulan transaksi yang telah dimodifikasi pengguna ke database.
     */
    public function saveImported(
        User $user,
        int $savingsId,
        array $transactions,
        bool $updateBalance = false,
        ?float $finalBalance = null,
        bool $skipDuplicates = false
    ): array {
        $saving = Saving::where('id', $savingsId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $savedCount = 0;
        $duplicatesSkipped = 0;
        $totalAmount = 0.0;

        DB::transaction(function () use ($user, $saving, $transactions, $updateBalance, $finalBalance, $skipDuplicates, &$savedCount, &$duplicatesSkipped, &$totalAmount) {
            foreach ($transactions as $trans) {
                // Abaikan jika tidak dipilih (unselected)
                if (isset($trans['is_selected']) && !$trans['is_selected']) {
                    continue;
                }

                $amount = (float) ($trans['amount'] ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                $type = in_array($trans['type'] ?? '', ['Cash In', 'Cash Out', 'Transfer', 'Tabungan'])
                    ? $trans['type']
                    : 'Cash Out';

                $description = trim($trans['description'] ?? 'Transaksi Import PDF');
                $transactionDate = !empty($trans['transaction_date'])
                    ? Carbon::parse($trans['transaction_date'])->format('Y-m-d')
                    : now()->format('Y-m-d');

                // Jika proteksi duplikat diaktifkan, lewati transaksi yang sudah ada
                if ($skipDuplicates) {
                    $existing = $this->duplicateHandler->findDuplicate(
                        $user->id,
                        $saving->id,
                        $description,
                        $amount,
                        $transactionDate
                    );

                    if ($existing) {
                        $duplicatesSkipped++;
                        continue;
                    }
                }

                $categoryId = !empty($trans['category_id']) ? (int) $trans['category_id'] : null;
                $budgetItemId = !empty($trans['budget_item_id']) ? (int) $trans['budget_item_id'] : null;

                // Verifikasi keberadaan category_id
                if ($categoryId && !Category::where('id', $categoryId)->exists()) {
                    $categoryId = null;
                }

                // Verifikasi keberadaan budget_item_id milik user
                if ($budgetItemId) {
                    $itemExists = BudgetItem::where('id', $budgetItemId)
                        ->whereHas('budget', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })->exists();
                    if (!$itemExists) {
                        $budgetItemId = null;
                    }
                }

                $direction = ($type === 'Cash In') ? Transaction::DIRECTION_IN : Transaction::DIRECTION_OUT;

                Transaction::updateOrCreate(
                    [
                        'user_id'          => $user->id,
                        'savings_id'       => $saving->id,
                        'transaction_date' => $transactionDate,
                        'amount'           => $amount,
                        'description'      => $description,
                    ],
                    [
                        'type'           => $type,
                        'direction'      => $direction,
                        'category_id'    => $categoryId,
                        'budget_item_id' => $budgetItemId,
                        'fee'            => 0,
                        'is_manual'      => false,
                    ]
                );

                $savedCount++;
                $totalAmount += $amount;
            }

            // Update saldo rekening jika opsi dicentang dan final balance ada
            if ($updateBalance && $finalBalance !== null) {
                $saving->update([
                    'current_balance' => $finalBalance,
                ]);

                Log::info('Saldo rekening diperbarui dari import PDF', [
                    'savings_id'    => $saving->id,
                    'final_balance' => $finalBalance,
                ]);
            }
        });

        return [
            'count'               => $savedCount,
            'duplicates_skipped'  => $duplicatesSkipped,
            'total_amount'        => $totalAmount,
            'final_balance'       => $finalBalance,
            'saving_name'         => $saving->name,
        ];
    }
}

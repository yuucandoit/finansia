<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected ?string $apiKey;
    protected string $primaryModel;
    protected string $fallbackModel;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', env('OPENROUTER_API_KEY'));
        $this->primaryModel = config('services.openrouter.primary_model', env('OPENROUTER_PRIMARY_MODEL', 'nex-agi/nex-n2.5-mini:free'));
        $this->fallbackModel = config('services.openrouter.fallback_model', env('OPENROUTER_FALLBACK_MODEL', 'inclusionai/ling-3.0-flash-fin:free'));
    }

    /**
     * Menganalisis pesan pengguna untuk mengekstrak intent dan data (transaksi atau anggaran, single maupun multiple).
     */
    public function parseMessage(string $userMessage, array $userAccounts = [], array $userCategories = []): array
    {
        $accountsList = implode(', ', $userAccounts) ?: 'Tunai, Rekening Utama';
        $categoriesList = implode(', ', $userCategories) ?: 'Makanan & Minuman, Transportasi, Belanja, Tagihan, Lain-lain';
        $currentDate = now()->format('Y-m-d');
        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;

        $systemPrompt = <<<PROMPT
 Anda adalah asisten keuangan pribadi cerdas untuk aplikasi Finansia.
Tugas Anda mengidentifikasi maksud (intent) pengguna dan mengekstrak informasi keuangan.
Pengguna dapat mencatat SATU atau BEBERAPA (MULTIPLE) transaksi cashflow maupun anggaran bulanan per kebutuhan sekaligus dalam satu pesan!

Konteks pengguna:
- Tanggal hari ini: {$currentDate}
- Bulan/Tahun saat ini: {$currentMonth}/{$currentYear}
- Daftar Rekening/Dompet yang tersedia: [{$accountsList}]
- Daftar Kategori/Kebutuhan yang tersedia: [{$categoriesList}]

Aturan Wajib:
1. Kembalikan HANYA format JSON valid tanpa tanda markdown (tanpa ```json ... ```), tanpa teks pengantar.
2. Skema JSON:
{
  "intent": "RECORD_TRANSACTION" | "RECORD_BUDGET" | "FINANCIAL_QUERY" | "GENERAL_CHAT",
  "data": {
    "items": [
      // Jika RECORD_TRANSACTION:
      {
        "type": "cash_out" | "cash_in" | "transfer",
        "amount": integer_nominal,
        "account_name": "string (sesuaikan paling mirip dari daftar rekening)",
        "category": "string (sesuaikan paling mirip dari daftar kategori)",
        "description": "string (deskripsi ringkas transaksi, contoh: 'Beli Kopi', 'Makan Ramen', 'Bensin')",
        "date": "YYYY-MM-DD"
      }
      // ATAU Jika RECORD_BUDGET:
      {
        "name": "string (nama pos/kebutuhan spesifik, contoh: 'Kontrakan', 'Listrik', 'Wifi', 'Nabung Mamah', 'Bensin', 'Makan')",
        "category": "string (nama kategori master standar: 'Makanan & Minuman', 'Transportasi', 'Utilitas & Tagihan', 'Belanja', 'Hiburan', 'Kesehatan', 'Tempat Tinggal', 'Keluarga & Sedekah', 'Tabungan & Investasi', 'Pendidikan', 'Lainnya')",
        "amount": integer_nominal,
        "month": integer_bulan (1-12, default {$currentMonth}),
        "year": integer_tahun (default {$currentYear})
      }
    ]
  } | null,
  "reply": "string (pesan balasan singkat dan ramah)"
}

3. Skema Intent:
- "RECORD_TRANSACTION": Jika user ingin mencatat 1 atau lebih transaksi cashflow (contoh: "makan ramen 45rb", "beli kopi 25k dan bensin 30k pake bca", "gaji masuk 5jt"). Isi "data.items" dengan array transaksi.
- "RECORD_BUDGET": Jika user ingin menetapkan/mengatur/mencatat 1 atau lebih batas anggaran bulanan per kebutuhan (contoh: "atur anggaran makan 1.5jt", "catat budget kontrakan 1.5jt, listrik 300rb, dan sedekah mamah 500rb", "anggaran skincare 300rb"). Berikan "name" sebagai nama pos kebutuhan spesifik (misal: "Kontrakan", "Listrik", "Sedekah Mamah") dan "category" sebagai kategori master standar keuangan (misal: Tempat Tinggal, Utilitas & Tagihan, Keluarga & Sedekah, Belanja, Makanan & Minuman). Isi "data.items" dengan array objek anggaran.
- "FINANCIAL_QUERY": Jika user bertanya tentang saldo, pengeluaran, sisa kas, perbandingan kategori, ATAU status anggaran bulanan (contoh: "bulan ini jajan makan udah habis berapa?", "sisa saldo berapa?", "apakah budget makan masih aman?"). Set "data": null.
- "GENERAL_CHAT": Jika sekadar menyapa atau ngobrol umum di luar niat catat atau tanya data keuangan (contoh: "halo", "apa kabar", "siapa kamu"). Set "data": null.
PROMPT;

        if (!empty($this->apiKey)) {
            // Coba model primer
            $response = $this->callApi($this->primaryModel, $systemPrompt, $userMessage);

            if (!$response || isset($response['error'])) {
                Log::warning('OpenRouter primary model failed, switching to fallback model.');
                $response = $this->callApi($this->fallbackModel, $systemPrompt, $userMessage);
            }

            if ($response && !isset($response['error'])) {
                $parsed = $this->cleanAndParseJson($response);
                if (!empty($parsed) && in_array($parsed['intent'] ?? '', ['RECORD_TRANSACTION', 'RECORD_BUDGET', 'FINANCIAL_QUERY', 'GENERAL_CHAT'])) {
                    // Normalisasi struktur items jika model mengembalikan flat data
                    if (isset($parsed['data']) && is_array($parsed['data'])) {
                        if (!isset($parsed['data']['items'])) {
                            if (isset($parsed['data']['amount'])) {
                                $parsed['data']['items'] = [$parsed['data']];
                            }
                        }
                    }
                    return $parsed;
                }
            }
        }

        // Fallback ke Intelligent Local Indonesian Parser jika AI offline/limit
        Log::info('Using fallback local parser for message: ' . $userMessage);
        return $this->fallbackLocalParser($userMessage, $userAccounts, $userCategories);
    }

    /**
     * Menjawab pertanyaan analisis keuangan pengguna berdasarkan data riil snapshot.
     */
    public function answerFinancialQuery(string $userMessage, array $financialSnapshot): string
    {
        $snapshotJson = json_encode($financialSnapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
Anda adalah asisten keuangan pribadi cerdas untuk Finansia.
Jawab pertanyaan pengguna HANYA berdasarkan snapshot data keuangan berikut (termasuk saldo, cash flow, dan anggaran bulanan jika ada):
{$snapshotJson}

Aturan Menjawab:
1. Jawab santai, ramah, to-the-point, dan gunakan format nominal Rupiah yang rapi (contoh: Rp 45.000, Rp 1.500.000).
2. Jangan pernah mengarang angka. Jika data yang ditanya tidak ada di snapshot, jawab jujur bahwa datanya belum tercatat.
3. Jika ditanya tentang anggaran atau batas pengeluaran, jelaskan statusnya (aman, waspada, atau overbudget) dan sisa anggaran yang masih bisa dipakai.
PROMPT;

        if (!empty($this->apiKey)) {
            $response = $this->callApi($this->primaryModel, $systemPrompt, $userMessage);

            if (!$response || isset($response['error'])) {
                $response = $this->callApi($this->fallbackModel, $systemPrompt, $userMessage);
            }

            $content = $response['choices'][0]['message']['content'] ?? null;
            if (!empty($content)) {
                return trim($content);
            }
        }

        // Fallback cerdas lokal jika AI offline
        return $this->fallbackFinancialAnswer($userMessage, $financialSnapshot);
    }

    protected function callApi(string $model, string $systemPrompt, string $userMessage): ?array
    {
        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => config('app.url', 'http://localhost:8000'),
                'X-Title'       => 'Finansia App',
                'Content-Type'  => 'application/json',
            ])->timeout(12)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.1,
            ]);

            if ($res->successful()) {
                return $res->json();
            } else {
                Log::warning('OpenRouter API returned non-200: ' . $res->status() . ' - ' . $res->body());
            }
        } catch (\Throwable $e) {
            Log::error('OpenRouter call error: ' . $e->getMessage());
        }

        return null;
    }

    protected function cleanAndParseJson(?array $rawResponse): array
    {
        if (!$rawResponse || empty($rawResponse['choices'][0]['message']['content'])) {
            return [];
        }

        $rawText = trim($rawResponse['choices'][0]['message']['content']);
        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawText);
        $decoded = json_decode(trim($cleanJson), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $decoded;
    }

    /**
     * Fallback cerdas lokal bahasa Indonesia jika API AI OpenRouter sedang limit / offline.
     * Mampu mendeteksi single & multiple cashflow transactions, serta single & multiple budget recordings!
     */
    public function fallbackLocalParser(string $message, array $userAccounts = [], array $userCategories = []): array
    {
        $lowerMsg = strtolower($message);

        // 1. Deteksi Pertanyaan Finansial (FINANCIAL_QUERY)
        $isFinancialQuery = preg_match('/\b(?:berapa|habis berapa|sisa|cek|total|kondisi|rekap|ringkasan|pengeluaran terbesar|aman gak|aman ga|masih aman)\b/i', $lowerMsg)
            && preg_match('/\b(?:saldo|uang|kas|pengeluaran|pemasukan|jajan|makan|kategori|belanja|bulan ini|anggaran|budget)\b/i', $lowerMsg);

        if ($isFinancialQuery) {
            return [
                'intent' => 'FINANCIAL_QUERY',
                'data'   => null,
                'reply'  => 'Pertanyaan keuangan terdeteksi.'
            ];
        }

        // 2. Deteksi Sapaan Umum (GENERAL_CHAT)
        $greetings = ['halo', 'hai', 'hello', 'hi', 'selamat pagi', 'selamat siang', 'selamat malam', 'pagi', 'siang', 'malam', 'assalamualaikum', 'ping', 'p'];
        $isGreeting = false;
        foreach ($greetings as $g) {
            if (str_starts_with(trim($lowerMsg), $g) || trim($lowerMsg) === $g) {
                $isGreeting = true;
                break;
            }
        }
        if ($isGreeting || preg_match('/^(?:siapa kamu|kamu siapa|bisa apa|help|bantuan)\??$/i', trim($lowerMsg))) {
            return [
                'intent' => 'GENERAL_CHAT',
                'data'   => null,
                'reply'  => 'Halo! Saya Asisten AI Finansia 🤖 Saya siap membantu mencatat transaksi (bisa beberapa sekaligus!) atau mengatur anggaran bulanan Anda. Coba ketik: "Atur anggaran makan 1.5jt dan belanja 500rb", atau: "Beli kopi 25k dan bensin 30k pake BCA".'
            ];
        }

        // 3. Deteksi Penetapan Anggaran (RECORD_BUDGET)
        $isBudgetIntent = preg_match('/\b(?:anggaran|budget|plafon|batas pengeluaran|alokasi)\b/i', $lowerMsg);

        if ($isBudgetIntent) {
            $budgetItems = $this->extractMultipleBudgets($message, $userCategories);
            if (!empty($budgetItems)) {
                return [
                    'intent' => 'RECORD_BUDGET',
                    'data'   => [
                        'items' => $budgetItems,
                    ],
                    'reply'  => count($budgetItems) > 1
                        ? 'Saya menemukan ' . count($budgetItems) . ' rencana anggaran kategori. Silakan periksa rincian di bawah ini dan konfirmasi:'
                        : 'Saya telah menyiapkan penetapan anggaran kategori Anda. Silakan periksa dan konfirmasi:'
                ];
            }
        }

        // 4. Deteksi Pencatatan Transaksi Cashflow (RECORD_TRANSACTION) - Mendukung Single & Multiple
        $transactionItems = $this->extractMultipleTransactions($message, $userAccounts, $userCategories);

        if (!empty($transactionItems)) {
            return [
                'intent' => 'RECORD_TRANSACTION',
                'data'   => [
                    'items' => $transactionItems,
                ],
                'reply'  => count($transactionItems) > 1
                    ? 'Saya menemukan ' . count($transactionItems) . ' transaksi dalam pesan Anda. Silakan tinjau dan konfirmasi untuk menyimpan ke database:'
                    : 'Saya telah menganalisis transaksi Anda. Silakan tinjau dan konfirmasi untuk menyimpan ke database:'
            ];
        }

        return [
            'intent' => 'GENERAL_CHAT',
            'data'   => null,
            'reply'  => 'Saya belum menemukan nominal transaksi atau anggaran yang jelas. Contoh format: "Beli bensin 30k pake BCA", "Atur anggaran makan 1.5jt", atau "Bulan ini pengeluaran saya berapa?".'
        ];
    }

    /**
     * Mengekstrak beberapa anggaran kebutuhan sekaligus dari pesan pengguna.
     */
    public function extractMultipleBudgets(string $message, array $userCategories = []): array
    {
        $currentMonth = (int) now()->month;
        $currentYear  = (int) now()->year;

        // Split berdasarkan kata penghubung atau tanda baca: koma, "dan", "serta", "sama", "lalu"
        $clauses = preg_split('/(?:\s*(?:dan|serta|sama|lalu|,)\s*)+/i', $message);
        $items = [];

        foreach ($clauses as $clause) {
            $clause = trim($clause);
            if (empty($clause)) {
                continue;
            }

            $amount = $this->extractAmountFromString($clause);
            if ($amount >= 1000) {
                $category = $this->detectCategoryFromString($clause, 'cash_out', $userCategories);
                $name = $this->extractBudgetNameFromString($clause);
                if (empty($name) || strtolower($name) === 'lainnya') {
                    $name = $category;
                }

                $items[] = [
                    'name'     => $name,
                    'category' => $category,
                    'amount'   => $amount,
                    'month'    => $currentMonth,
                    'year'     => $currentYear,
                ];
            }
        }

        // Jika split gagal menemukan lebih dari 0, coba ekstrak global
        if (empty($items)) {
            $amount = $this->extractAmountFromString($message);
            if ($amount >= 1000) {
                $category = $this->detectCategoryFromString($message, 'cash_out', $userCategories);
                $name = $this->extractBudgetNameFromString($message);
                if (empty($name) || strtolower($name) === 'lainnya') {
                    $name = $category;
                }

                $items[] = [
                    'name'     => $name,
                    'category' => $category,
                    'amount'   => $amount,
                    'month'    => $currentMonth,
                    'year'     => $currentYear,
                ];
            }
        }

        return $items;
    }

    /**
     * Mengekstrak beberapa transaksi sekaligus dari pesan pengguna.
     */
    public function extractMultipleTransactions(string $message, array $userAccounts = [], array $userCategories = []): array
    {
        // Deteksi apakah ada rekening global yang disebut (misal: "pake bca" di akhir)
        $globalAccount = $this->detectAccountFromString($message, $userAccounts);

        // Split pesan jika ada tanda koma atau kata penghubung
        $clauses = preg_split('/(?:\s*(?:dan|serta|sama|lalu|,)\s*)+/i', $message);
        $items = [];

        foreach ($clauses as $clause) {
            $clause = trim($clause);
            if (empty($clause)) {
                continue;
            }

            $amount = $this->extractAmountFromString($clause);
            if ($amount > 0) {
                $type = $this->detectTypeFromString($clause);
                $category = $this->detectCategoryFromString($clause, $type, $userCategories);
                $account = $this->detectAccountFromString($clause, $userAccounts) ?: $globalAccount;
                $desc = $this->generateCleanDescription($clause, $category, $type);

                $date = now()->format('Y-m-d');
                if (str_contains(strtolower($clause), 'kemarin')) {
                    $date = now()->subDay()->format('Y-m-d');
                }

                $items[] = [
                    'type'         => $type,
                    'amount'       => $amount,
                    'account_name' => $account,
                    'category'     => $category,
                    'description'  => $desc,
                    'date'         => $date,
                ];
            }
        }

        // Jika pemisahan klausa tidak menghasilkan item tapi pesan global memiliki amount
        if (empty($items)) {
            $amount = $this->extractAmountFromString($message);
            if ($amount > 0) {
                $type = $this->detectTypeFromString($message);
                $category = $this->detectCategoryFromString($message, $type, $userCategories);
                $account = $globalAccount;
                $desc = $this->generateCleanDescription($message, $category, $type);

                $date = now()->format('Y-m-d');
                if (str_contains(strtolower($message), 'kemarin')) {
                    $date = now()->subDay()->format('Y-m-d');
                }

                $items[] = [
                    'type'         => $type,
                    'amount'       => $amount,
                    'account_name' => $account,
                    'category'     => $category,
                    'description'  => $desc,
                    'date'         => $date,
                ];
            }
        }

        return $items;
    }

    /**
     * Ekstrak nominal uang dari cuplikan teks string.
     */
    public function extractAmountFromString(string $str): int
    {
        $lower = strtolower($str);
        if (preg_match('/(?:rp\.?\s*)?(\d+(?:[.,]\d+)?)\s*(rb|k|ribu|rebu|jt|juta)?\b/i', $lower, $matches)) {
            $rawNumber = str_replace(',', '.', $matches[1]);
            $multiplier = 1;
            $unit = strtolower($matches[2] ?? '');

            if (in_array($unit, ['rb', 'k', 'ribu', 'rebu'])) {
                $multiplier = 1000;
            } elseif (in_array($unit, ['jt', 'juta'])) {
                $multiplier = 1000000;
            } elseif (empty($unit) && (float)$rawNumber < 1000 && !str_contains($matches[0], 'rp')) {
                $multiplier = 1000;
            }

            return (int) round(((float)$rawNumber) * $multiplier);
        }

        return 0;
    }

    /**
     * Deteksi tipe transaksi (Cash In vs Cash Out) dari teks.
     */
    protected function detectTypeFromString(string $str): string
    {
        $lower = strtolower($str);
        $cashInKeywords = ['gaji', 'terima', 'dapat', 'masuk', 'pemasukan', 'bonus', 'transferan', 'cair', 'income', 'hasil'];
        foreach ($cashInKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return 'cash_in';
            }
        }
        return 'cash_out';
    }

    /**
     * Deteksi nama rekening yang paling cocok dari teks.
     */
    protected function detectAccountFromString(string $str, array $userAccounts = []): ?string
    {
        $lower = strtolower($str);
        foreach ($userAccounts as $acc) {
            if (str_contains($lower, strtolower($acc))) {
                return $acc;
            }
        }

        $bankKeywords = [
            'bca' => 'BCA', 'mandiri' => 'Mandiri', 'bri' => 'BRI',
            'bni' => 'BNI', 'gopay' => 'GoPay', 'ovo' => 'OVO',
            'dana' => 'Dana', 'shopeepay' => 'ShopeePay', 'jago' => 'Jago',
            'cash' => 'Dompet Tunai', 'tunai' => 'Dompet Tunai', 'uang tunai' => 'Dompet Tunai', 'dompet' => 'Dompet Tunai'
        ];

        foreach ($bankKeywords as $k => $bName) {
            if (str_contains($lower, $k)) {
                foreach ($userAccounts as $acc) {
                    if (stripos($acc, $bName) !== false || ($bName === 'Dompet Tunai' && stripos($acc, 'Cash') !== false)) {
                        return $acc;
                    }
                }
                return $bName;
            }
        }

        return !empty($userAccounts) ? $userAccounts[0] : null;
    }

    /**
     * Deteksi kategori dari cuplikan teks.
     */
    protected function detectCategoryFromString(string $str, string $type = 'cash_out', array $userCategories = []): string
    {
        $lower = strtolower($str);

        if ($type === 'cash_in') {
            if (str_contains($lower, 'gaji')) return 'Gaji';
            if (str_contains($lower, 'investasi') || str_contains($lower, 'saham') || str_contains($lower, 'reksadana')) return 'Investasi';
            if (str_contains($lower, 'freelance') || str_contains($lower, 'project') || str_contains($lower, 'proyek')) return 'Freelance / Project';
            if (str_contains($lower, 'bonus') || str_contains($lower, 'thr') || str_contains($lower, 'hadiah')) return 'Bonus & Hadiah';
            return 'Lainnya';
        }

        // Cek kecocokan kategori kustom pengguna terlebih dahulu (prioritas nama terpanjang)
        if (!empty($userCategories)) {
            $sortedUserCats = $userCategories;
            usort($sortedUserCats, fn($a, $b) => strlen($b) <=> strlen($a));
            foreach ($sortedUserCats as $uCat) {
                $uCatLower = strtolower($uCat);
                if ($uCatLower !== 'lainnya' && !empty($uCatLower) && str_contains($lower, $uCatLower)) {
                    return $uCat;
                }
            }
        }

        $catRules = [
            'Tempat Tinggal'      => ['kos', 'kost', 'kontrakan', 'sewa rumah', 'cicilan rumah', 'kpr', 'rumah', 'apartemen'],
            'Keluarga & Sedekah'  => ['sedekah', 'infaq', 'zakat', 'mamah', 'ibu', 'ayah', 'bapak', 'ortu', 'orang tua', 'keluarga', 'pegangan mamah'],
            'Tabungan & Investasi'=> ['nabung', 'tabungan', 'investasi', 'deposito', 'reksadana', 'saham', 'crypto', 'emas'],
            'Pendidikan'          => ['sekolah', 'kuliah', 'kursus', 'les', 'buku', 'spp', 'ujian', 'pendidikan'],
            'Makanan & Minuman'   => ['makan', 'minum', 'kopi', 'ramen', 'bakso', 'soto', 'nasgor', 'nasi', 'resto', 'restoran', 'cafe', 'snack', 'roti', 'ayam', 'sarapan', 'lunch', 'dinner', 'kuliner', 'jajan'],
            'Transportasi'        => ['bensin', 'pertalite', 'pertamax', 'solar', 'gojek', 'grab', 'ojol', 'maxim', 'parkir', 'tol', 'kereta', 'krl', 'mrt', 'angkot', 'bus', 'tiket', 'transport'],
            'Belanja'             => ['belanja', 'beli', 'indomaret', 'alfamart', 'supermarket', 'mall', 'shopee', 'tokopedia', 'baju', 'kaos', 'sepatu', 'celana', 'shopping', 'sembako'],
            'Utilitas & Tagihan'  => ['listrik', 'pln', 'air', 'pdam', 'wifi', 'indihome', 'biznet', 'internet', 'pulsa', 'kuota', 'telkomsel', 'tagihan', 'bpjs'],
            'Hiburan'             => ['nonton', 'bioskop', 'xxi', 'cinema', 'game', 'steam', 'top up', 'netflix', 'spotify', 'karaoke', 'liburan', 'jalan-jalan'],
            'Kesehatan'           => ['obat', 'dokter', 'apotek', 'apotik', 'vitamin', 'klinik', 'rumah sakit', 'rs']
        ];

        foreach ($catRules as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $catName;
                }
            }
        }

        return 'Lainnya';
    }

    /**
     * Ekstrak nama pos/kebutuhan anggaran spesifik dari string.
     * Contoh: "anggaran kontrakan 1.5jt" -> "Kontrakan", "listrik rumah 300rb" -> "Listrik Rumah".
     */
    public function extractBudgetNameFromString(string $str): string
    {
        $clean = strtolower($str);
        $clean = preg_replace('/\b(?:atur|buat|catat(?:kan)?|pasang|tetapkan|tambah(?:kan)?|masukkan|tolong|rencana(?:kan)?|alokasi(?:kan)?)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:anggaran|budget|plafon|batas(?:\s+pengeluaran)?|kebutuhan)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:untuk|buat|sebesar|senilai|nominal|sebulan|tiap\s+bulan|per\s+bulan|bulan\s+ini|ini|dong|nih|ya|deh)\b/i', '', $clean);
        $clean = preg_replace('/(?:rp\.?\s*)?\d+(?:[.,]\d+)?\s*(?:rb|k|ribu|rebu|jt|juta)?\b/i', '', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        $clean = trim($clean, " \t\n\r\0\x0B,.-_");

        if (!empty($clean) && strlen($clean) >= 2 && strlen($clean) <= 50) {
            return ucwords($clean);
        }

        return '';
    }

    /**
     * Deteksi nama kebutuhan (kategori) dari kalimat penetapan anggaran.
     * Mampu mendeteksi kebutuhan kustom seperti 'Kos', 'Sedekah', 'Gym', 'Skincare', 'Cicilan Motor', dll.
     */
    public function detectKebutuhanFromString(string $str, array $userCategories = []): string
    {
        $lower = strtolower($str);

        // 1. Cek apakah ada kategori milik user yang cocok (prioritas string terpanjang)
        if (!empty($userCategories)) {
            $sortedUserCats = $userCategories;
            usort($sortedUserCats, fn($a, $b) => strlen($b) <=> strlen($a));
            foreach ($sortedUserCats as $uCat) {
                $uCatLower = strtolower($uCat);
                if ($uCatLower !== 'lainnya' && !empty($uCatLower) && str_contains($lower, $uCatLower)) {
                    return $uCat;
                }
            }
        }

        // 2. Cek kategori standar bawaan
        $catRules = [
            'Tempat Tinggal'      => ['kos', 'kost', 'kontrakan', 'sewa rumah', 'cicilan rumah', 'kpr', 'rumah', 'apartemen'],
            'Keluarga & Sedekah'  => ['sedekah', 'infaq', 'zakat', 'mamah', 'ibu', 'ayah', 'bapak', 'ortu', 'orang tua', 'keluarga', 'pegangan mamah'],
            'Tabungan & Investasi'=> ['nabung', 'tabungan', 'investasi', 'deposito', 'reksadana', 'saham', 'crypto', 'emas'],
            'Pendidikan'          => ['sekolah', 'kuliah', 'kursus', 'les', 'buku', 'spp', 'ujian', 'pendidikan'],
            'Makanan & Minuman'   => ['makan', 'minum', 'kopi', 'ramen', 'bakso', 'soto', 'nasgor', 'nasi', 'resto', 'restoran', 'cafe', 'snack', 'roti', 'ayam', 'sarapan', 'lunch', 'dinner', 'kuliner', 'jajan'],
            'Transportasi'        => ['bensin', 'pertalite', 'pertamax', 'solar', 'gojek', 'grab', 'ojol', 'maxim', 'parkir', 'tol', 'kereta', 'krl', 'mrt', 'angkot', 'bus', 'tiket', 'transport'],
            'Belanja'             => ['belanja', 'beli', 'indomaret', 'alfamart', 'supermarket', 'mall', 'shopee', 'tokopedia', 'baju', 'kaos', 'sepatu', 'celana', 'shopping', 'sembako'],
            'Utilitas & Tagihan'  => ['listrik', 'pln', 'air', 'pdam', 'wifi', 'indihome', 'biznet', 'internet', 'pulsa', 'kuota', 'telkomsel', 'tagihan', 'bpjs'],
            'Hiburan'             => ['nonton', 'bioskop', 'xxi', 'cinema', 'game', 'steam', 'top up', 'netflix', 'spotify', 'karaoke', 'liburan', 'jalan-jalan'],
            'Kesehatan'           => ['obat', 'dokter', 'apotek', 'apotik', 'vitamin', 'klinik', 'rumah sakit', 'rs']
        ];

        foreach ($catRules as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return $catName;
                }
            }
        }

        // 3. Ekstrak nama kebutuhan spesifik dari kalimat dengan menghapus kata perintah, kata kunci budget, nominal & filler
        $posName = $this->extractBudgetNameFromString($str);
        if (!empty($posName)) {
            return $posName;
        }

        return 'Lainnya';
    }

    /**
     * Menjawab pertanyaan finansial secara langsung jika AI eksternal sedang offline.
     */
    protected function fallbackFinancialAnswer(string $userMessage, array $snapshot): string
    {
        $lower = strtolower($userMessage);
        $periode = $snapshot['periode_bulan'] ?? 'Bulan ini';
        $totalSaldo = 'Rp ' . number_format($snapshot['total_saldo'] ?? 0, 0, ',', '.');
        $pengeluaran = 'Rp ' . number_format($snapshot['cashflow_bulan_ini']['total_pengeluaran'] ?? 0, 0, ',', '.');
        $pemasukan = 'Rp ' . number_format($snapshot['cashflow_bulan_ini']['total_pemasukan'] ?? 0, 0, ',', '.');
        $sisaKas = 'Rp ' . number_format($snapshot['cashflow_bulan_ini']['sisa_kas'] ?? 0, 0, ',', '.');

        // Pertanyaan Anggaran Bulanan
        if (str_contains($lower, 'anggaran') || str_contains($lower, 'budget')) {
            $budgetData = $snapshot['anggaran_bulan_ini'] ?? null;
            if ($budgetData && $budgetData['total_anggaran'] > 0) {
                $totalB = 'Rp ' . number_format($budgetData['total_anggaran'], 0, ',', '.');
                $spentB = 'Rp ' . number_format($budgetData['total_terpakai'], 0, ',', '.');
                $remB = 'Rp ' . number_format(abs($budgetData['sisa_anggaran']), 0, ',', '.');
                $statusText = match ($budgetData['status_agregat'] ?? 'safe') {
                    'overbudget' => 'sudah melebihi batas (Overbudget)!',
                    'warning'    => 'sudah mendekati batas (Waspada)',
                    default      => 'masih aman',
                };

                return "Untuk {$periode}, total anggaran yang Anda tetapkan adalah {$totalB}. Pengeluaran Anda saat ini sebesar {$spentB} dan statusnya {$statusText}. Sisa anggaran: {$remB}.";
            } else {
                return "Anda belum menetapkan anggaran bulanan untuk {$periode}. Anda bisa mengaturnya di menu Anggaran atau ketik: 'Atur anggaran makan 1.5jt'.";
            }
        }

        // Pertanyaan kategori (misal: jajan makan)
        if (str_contains($lower, 'makan') || str_contains($lower, 'jajan') || str_contains($lower, 'minum')) {
            $amount = $snapshot['pengeluaran_per_kategori']['Makanan & Minuman'] ?? 0;
            $formatted = 'Rp ' . number_format($amount, 0, ',', '.');
            return "{$periode} kamu sudah menghabiskan {$formatted} untuk Makanan & Minuman.";
        }

        // Pertanyaan saldo
        if (str_contains($lower, 'saldo') || str_contains($lower, 'total uang')) {
            return "Total saldo kamu saat ini di semua rekening adalah {$totalSaldo}.";
        }

        // Pertanyaan pengeluaran total
        if (str_contains($lower, 'pengeluaran') || str_contains($lower, 'habis berapa')) {
            return "{$periode} total pengeluaranmu sebesar {$pengeluaran}. Total pemasukanmu {$pemasukan}, sehingga sisa kas bulan ini {$sisaKas}.";
        }

        return "Ringkasan keuanganmu ({$periode}): Total saldo {$totalSaldo}, pemasukan {$pemasukan}, dan pengeluaran {$pengeluaran}.";
    }

    /**
     * Membuat deskripsi transaksi yang ringkas, rapi, dan representatif dari kalimat user.
     */
    public function generateCleanDescription(string $message, string $category = 'Lainnya', string $type = 'cash_out'): string
    {
        $clean = strtolower($message);

        // Hapus kata pembuka / filler awal
        $clean = preg_replace('/^(?:halo|hai|tolong\s+catat(?:kan)?|catat(?:kan)?|aku|saya|gw|gue|kita)?\s*(?:baru saja|baru aja|barusan|tadi|kemarin|abis|habis)?\s*/i', '', $clean);

        // Hapus keterangan rekening / pembayaran di akhir
        $clean = preg_replace('/\b(?:pake|pakai|via|lewat|menggunakan|dari|ke)\s+[a-z0-9\s_-]+$/i', '', $clean);

        // Hapus keterangan nominal (seharga 25rb, sebesar 50k, 25ribu, dll)
        $clean = preg_replace('/\b(?:seharga|sebesar|senilai|nominal|harga)?\s*(?:rp\.?\s*)?\d+(?:[.,]\d+)?\s*(?:rb|k|ribu|rebu|jt|juta)?\b/i', '', $clean);

        // Hapus kata slang / pelengkap santai
        $clean = preg_replace('/\b(?:dong|nih|ya|sih|deh|bray|bro|min|please)\b/i', '', $clean);

        // Rapikan spasi dan tanda baca
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        $clean = trim($clean, " \t\n\r\0\x0B,.-_");

        if (empty($clean) || strlen($clean) < 3) {
            $prefix = $type === 'cash_in' ? 'Penerimaan' : 'Pembelian';
            return $category !== 'Lainnya' ? "{$prefix} {$category}" : ($type === 'cash_in' ? 'Pemasukan' : 'Pengeluaran');
        }

        return ucwords($clean);
    }
}
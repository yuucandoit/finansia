<?php

namespace App\Services;

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
     * Menganalisis pesan pengguna untuk mengekstrak intent dan data transaksi.
     */
    public function parseMessage(string $userMessage, array $userAccounts = [], array $userCategories = []): array
    {
        $accountsList = implode(', ', $userAccounts) ?: 'Tunai, Rekening Utama';
        $categoriesList = implode(', ', $userCategories) ?: 'Makanan & Minuman, Transportasi, Belanja, Tagihan, Lain-lain';
        $currentDate = now()->format('Y-m-d');

        $systemPrompt = <<<PROMPT
Anda adalah asisten keuangan pribadi cerdas untuk aplikasi Finansia.
Tugas Anda mengidentifikasi maksud (intent) pengguna dan mengekstrak informasi transaksi jika ada.

Konteks pengguna:
- Tanggal hari ini: {$currentDate}
- Daftar Rekening/Dompet yang tersedia: [{$accountsList}]
- Daftar Kategori yang tersedia: [{$categoriesList}]

Aturan Wajib:
1. Kembalikan HANYA format JSON valid tanpa tanda markdown (tanpa ```json ... ```), tanpa teks pengantar.
2. Skema JSON:
{
  "intent": "RECORD_TRANSACTION" | "FINANCIAL_QUERY" | "GENERAL_CHAT",
  "data": {
    "type": "cash_out" | "cash_in" | "transfer",
    "amount": integer_nominal,
    "account_name": "string (sesuaikan paling mirip dari daftar rekening)",
    "category": "string (sesuaikan paling mirip dari daftar kategori)",
    "description": "string (buatkan deskripsi transaksi yang ringkas dan jelas, contoh: 'Beli Kopi', 'Makan Ramen', 'Gaji Masuk')",
    "date": "YYYY-MM-DD"
  } | null,
  "reply": "string (pesan balasan singkat dan ramah)"
}

3. Skema Intent:
- "RECORD_TRANSACTION": Jika user ingin mencatat pemasukan/pengeluaran/transfer (contoh: "makan ramen 45rb", "beli kopi 25k pake bca", "gaji masuk 5jt"). Set data dengan rincian transaksi.
- "FINANCIAL_QUERY": Jika user bertanya tentang saldo, pengeluaran, sisa kas, perbandingan kategori, atau kondisi uangnya (contoh: "bulan ini jajan makan udah habis berapa?", "sisa saldo berapa?", "pengeluaran terbesar apa?"). Set "data": null.
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
                if (!empty($parsed) && in_array($parsed['intent'] ?? '', ['RECORD_TRANSACTION', 'FINANCIAL_QUERY', 'GENERAL_CHAT'])) {
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
Jawab pertanyaan pengguna HANYA berdasarkan snapshot data keuangan berikut:
{$snapshotJson}

Aturan Menjawab:
1. Jawab santai, ramah, to-the-point, dan gunakan format nominal Rupiah yang rapi (contoh: Rp 45.000, Rp 1.500.000).
2. Jangan pernah mengarang angka. Jika data yang ditanya tidak ada di snapshot, jawab jujur bahwa datanya belum tercatat.
3. Berikan insight singkat jika relevan (misalnya mengingatkan pengeluaran terbesar atau sisa kas bulan ini).
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
                'HTTP-Referer' => config('app.url', 'http://localhost:8000'),
                'X-Title' => 'Finansia App',
                'Content-Type' => 'application/json',
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
     */
    public function fallbackLocalParser(string $message, array $userAccounts = [], array $userCategories = []): array
    {
        $lowerMsg = strtolower($message);

        // 1. Deteksi Pertanyaan Finansial (FINANCIAL_QUERY)
        $isFinancialQuery = preg_match('/\b(?:berapa|habis berapa|sisa|cek|total|kondisi|rekap|ringkasan|pengeluaran terbesar)\b/i', $lowerMsg)
            && preg_match('/\b(?:saldo|uang|kas|pengeluaran|pemasukan|jajan|makan|kategori|belanja|bulan ini)\b/i', $lowerMsg);

        if ($isFinancialQuery) {
            return [
                'intent' => 'FINANCIAL_QUERY',
                'data' => null,
                'reply' => 'Pertanyaan keuangan terdeteksi.'
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
                'data' => null,
                'reply' => 'Halo! Saya Asisten AI Finansia 🤖 Saya siap membantu mencatat transaksi keuangan atau menjawab pertanyaan seputar keuangan Anda. Coba tanyakan: "Bulan ini jajan makan udah habis berapa?" atau ketik: "Barusan makan ramen 45rb pake GoPay".'
            ];
        }

        // 3. Ekstrak Nominal Uang
        $amount = 0;
        if (preg_match('/(?:rp\.?\s*)?(\d+(?:[.,]\d+)?)\s*(rb|k|ribu|rebu|jt|juta)?\b/i', $lowerMsg, $matches)) {
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

            $amount = (int)((float)$rawNumber * $multiplier);
        }

        if ($amount <= 0) {
            return [
                'intent' => 'GENERAL_CHAT',
                'data' => null,
                'reply' => 'Saya belum menemukan nominal transaksi atau maksud pertanyaan Anda. Contoh format: "Beli bensin 30k pake BCA" atau "Bulan ini pengeluaran saya berapa?".'
            ];
        }

        // 4. Deteksi Tipe (Cash In vs Cash Out)
        $cashInKeywords = ['gaji', 'terima', 'dapat', 'masuk', 'pemasukan', 'bonus', 'transferan', 'cair', 'income', 'hasil'];
        $type = 'cash_out';
        foreach ($cashInKeywords as $kw) {
            if (str_contains($lowerMsg, $kw)) {
                $type = 'cash_in';
                break;
            }
        }

        // 5. Deteksi Rekening
        $matchedAccount = null;
        foreach ($userAccounts as $acc) {
            if (str_contains($lowerMsg, strtolower($acc))) {
                $matchedAccount = $acc;
                break;
            }
        }
        if (!$matchedAccount && !empty($userAccounts)) {
            $bankKeywords = [
                'bca' => 'BCA', 'mandiri' => 'Mandiri', 'bri' => 'BRI',
                'bni' => 'BNI', 'gopay' => 'GoPay', 'ovo' => 'OVO',
                'dana' => 'Dana', 'shopeepay' => 'ShopeePay', 'jago' => 'Jago',
                'tunai' => 'Tunai', 'cash' => 'Tunai'
            ];
            foreach ($bankKeywords as $k => $bName) {
                if (str_contains($lowerMsg, $k)) {
                    foreach ($userAccounts as $acc) {
                        if (stripos($acc, $bName) !== false) {
                            $matchedAccount = $acc;
                            break 2;
                        }
                    }
                    $matchedAccount = $bName;
                    break;
                }
            }
            if (!$matchedAccount) {
                $matchedAccount = $userAccounts[0];
            }
        }

        // 6. Deteksi Kategori
        $matchedCategory = 'Lainnya';
        if ($type === 'cash_in') {
            if (str_contains($lowerMsg, 'gaji')) $matchedCategory = 'Gaji';
            elseif (str_contains($lowerMsg, 'investasi') || str_contains($lowerMsg, 'saham') || str_contains($lowerMsg, 'reksadana')) $matchedCategory = 'Investasi';
            elseif (str_contains($lowerMsg, 'freelance') || str_contains($lowerMsg, 'project') || str_contains($lowerMsg, 'proyek')) $matchedCategory = 'Freelance / Project';
            elseif (str_contains($lowerMsg, 'bonus') || str_contains($lowerMsg, 'thr') || str_contains($lowerMsg, 'hadiah')) $matchedCategory = 'Bonus & Hadiah';
        } else {
            $catRules = [
                'Makanan & Minuman' => ['makan', 'minum', 'kopi', 'ramen', 'bakso', 'soto', 'nasgor', 'nasi', 'resto', 'restoran', 'cafe', 'snack', 'roti', 'ayam', 'sarapan', 'lunch', 'dinner'],
                'Transportasi' => ['bensin', 'pertalite', 'pertamax', 'solar', 'gojek', 'grab', 'ojol', 'maxim', 'parkir', 'tol', 'kereta', 'krl', 'mrt', 'angkot', 'bus', 'tiket'],
                'Belanja' => ['belanja', 'beli', 'indomaret', 'alfamart', 'supermarket', 'mall', 'shopee', 'tokopedia', 'baju', 'kaos', 'sepatu', 'celana'],
                'Utilitas & Tagihan' => ['listrik', 'pln', 'air', 'pdam', 'wifi', 'indihome', 'biznet', 'internet', 'pulsa', 'kuota', 'telkomsel', 'tagihan', 'bpjs'],
                'Hiburan' => ['nonton', 'bioskop', 'xxi', 'cinema', 'game', 'steam', 'top up', 'netflix', 'spotify', 'karaoke', 'liburan', 'jalan-jalan'],
                'Kesehatan' => ['obat', 'dokter', 'apotek', 'apotik', 'vitamin', 'klinik', 'rumah sakit', 'rs']
            ];

            foreach ($catRules as $catName => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($lowerMsg, $kw)) {
                        $matchedCategory = $catName;
                        break 2;
                    }
                }
            }
        }

        // 7. Tanggal
        $date = now()->format('Y-m-d');
        if (str_contains($lowerMsg, 'kemarin')) {
            $date = now()->subDay()->format('Y-m-d');
        }

        // 8. Deskripsi Ringkas Otomatis
        $description = $this->generateCleanDescription($message, $matchedCategory, $type);

        return [
            'intent' => 'RECORD_TRANSACTION',
            'data' => [
                'type' => $type,
                'amount' => $amount,
                'account_name' => $matchedAccount,
                'category' => $matchedCategory,
                'description' => $description,
                'date' => $date,
            ],
            'reply' => 'Saya telah menganalisis transaksi Anda. Silakan tinjau dan konfirmasi untuk menyimpan ke database:'
        ];
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
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
        $this->primaryModel = config('services.openrouter.primary_model', env('OPENROUTER_PRIMARY_MODEL', 'google/gemma-4-26b-a4b-it:free'));
        $this->fallbackModel = config('services.openrouter.fallback_model', env('OPENROUTER_FALLBACK_MODEL', 'openrouter/free'));
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
  "intent": "RECORD_TRANSACTION" | "RECORD_BUDGET" | "RECORD_FUTURE_PLAN" | "FINANCIAL_QUERY" | "FINANCIAL_ADVISOR" | "GENERAL_CHAT",
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
- "RECORD_FUTURE_PLAN": Jika user meminta dibuatkan rencana masa depan / proyek tabungan / daftar item beserta estimasi biaya & jadwal (contoh: "buatkan rencana rakit PC budget 15jt", "buat future plan nikah 100jt 6 bulan lagi", "rencana liburan ke Bali 8jt bulan depan"). Set "data": null (akan diproses oleh generator rencana).
- "FINANCIAL_QUERY": Jika user bertanya angka spesifik tentang saldo, pengeluaran, sisa kas, perbandingan kategori, ATAU status anggaran bulanan (contoh: "bulan ini jajan makan udah habis berapa?", "sisa saldo berapa?"). Set "data": null.
- "FINANCIAL_ADVISOR": Jika user meminta saran, konsultasi, evaluasi kesehatan finansial, strategi menabung, cara hemat, analisis keborosan, strategi pelunasan hutang/piutang, atau kelayakan target Future Plan (contoh: "cek kondisi keuanganku bulan ini", "aku boros di mana ya?", "gimana strategi lunasin hutang?", "apakah target nikahku realistis?"). Set "data": null.
- "GENERAL_CHAT": Jika sekadar menyapa atau ngobrol umum di luar pencatatan maupun konsultasi keuangan (contoh: "halo", "apa kabar", "siapa kamu"). Set "data": null.
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
                if (!empty($parsed) && in_array($parsed['intent'] ?? '', ['RECORD_TRANSACTION', 'RECORD_BUDGET', 'RECORD_FUTURE_PLAN', 'FINANCIAL_QUERY', 'FINANCIAL_ADVISOR', 'GENERAL_CHAT'])) {
                    // Normalisasi struktur items jika model mengembalikan flat data
                    if (isset($parsed['data']) && is_array($parsed['data'])) {
                        if (!isset($parsed['data']['items'])) {
                            if (isset($parsed['data']['amount'])) {
                                $parsed['data']['items'] = [$parsed['data']];
                            }
                        }
                    }

                    // Guardrail: jika user memberikan banyak item tapi LLM hanya menangkap sebagian/1 item, gunakan parser lokal yang lebih lengkap
                    if (in_array($parsed['intent'], ['RECORD_BUDGET', 'RECORD_TRANSACTION'])) {
                        $localParsed = $this->fallbackLocalParser($userMessage, $userAccounts, $userCategories);
                        $llmItemCount = count($parsed['data']['items'] ?? []);
                        $localItemCount = count($localParsed['data']['items'] ?? []);

                        if ($localItemCount > $llmItemCount) {
                            Log::info("Local parser found {$localItemCount} items vs LLM {$llmItemCount} items, using richer local parser result.");
                            return $localParsed;
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
    public function answerFinancialQuery(string $userMessage, array $financialSnapshot, array $history = []): string
    {
        return $this->adviseUser($userMessage, $financialSnapshot, $history);
    }

    /**
     * Financial Advisor AI: Berbincang multi-turn dengan pengguna, menganalisis kondisi keuangan riil,
     * memberikan evaluasi kesehatan finansial, strategi hemat, hutang, dan rencana masa depan.
     */
    public function adviseUser(string $userMessage, array $financialSnapshot, array $history = []): string
    {
        $snapshotJson = json_encode($financialSnapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $systemPrompt = <<<PROMPT
Anda adalah **Finansia Advisor**, penasihat keuangan pribadi profesional, hangat, dan suportif di aplikasi Finansia.
Tugas Anda adalah berbincang secara interaktif dengan pengguna, menjawab pertanyaan keuangan, serta memberikan saran dan strategi finansial yang praktis berdasarkan data riil pengguna berikut ini:

=== DATA KEUANGAN PENGGUNA SAAT INI ===
{$snapshotJson}
========================================

Pedoman Finansia Advisor:
1. Selalu gunakan data riil dari snapshot di atas (saldo rekening, cashflow, savings_rate_persen, rasio_dana_darurat_bulan, pengeluaran_per_kategori, anggaran_bulan_ini, rencana_masa_depan, hutang_piutang, transaksi_rutin_aktif).
2. Jangan pernah mengarang angka. Jika suatu pos belum ada datanya, sampaikan jujur dengan ramah.
3. Gunakan format nominal Rupiah yang rapi (contoh: Rp 1.500.000) dan formatting ringan (**tebal** untuk angka/poin penting, daftar poin `- ` agar mudah dibaca di layar HP).
4. Berikan insight & langkah konkret:
   - Jika ditanya kondisi keuangan: evaluasi Savings Rate (ideal >= 20%), Dana Darurat (ideal >= 3-6 bulan pengeluaran), dan pos pengeluaran terbesar.
   - Jika ada anggaran yang overbudget/warning, ingatkan dengan konstruktif.
   - Jika ada hutang aktif atau Future Plan, bantu susun prioritas alokasi dana bulanan secara realistis.
5. Gunakan bahasa Indonesia yang santai namun profesional, empatik, dan mengajak berdiskusi (boleh akhiri dengan 1 pertanyaan lanjutan singkat bila relevan).
PROMPT;

        if (!empty($this->apiKey)) {
            $response = $this->callApi($this->primaryModel, $systemPrompt, $userMessage, $history, 0.4);

            if (!$response || isset($response['error'])) {
                $response = $this->callApi($this->fallbackModel, $systemPrompt, $userMessage, $history, 0.4);
            }

            $content = $response['choices'][0]['message']['content'] ?? null;
            if (!empty($content)) {
                return trim($content);
            }
        }

        // Fallback cerdas lokal jika AI offline
        return $this->fallbackFinancialAnswer($userMessage, $financialSnapshot);
    }

    /**
     * Parsing teks e-statement bank apapun menggunakan AI untuk menghasilkan array transaksi terstruktur.
     */
    public function parseBankStatementWithAi(string $statementText, array $categories = []): array
    {
        $currentYear = (int) now()->year;

        $systemPrompt = <<<PROMPT
Anda adalah AI Spesialis Ekstraksi Mutasi Bank & Rekening Koran untuk aplikasi Finansia.
Tugas Anda: Membaca teks e-statement bank apapun dan mengekstrak SELURUH baris mutasi transaksi uang menjadi JSON array yang valid dan bersih.

Format output JSON yang WAJIB dihasilkan:
[
  {
    "date": "YYYY-MM-DD",
    "raw_date": "Tanggal asli di dokumen (cth: 25 Sep 2026)",
    "description": "Keterangan transaksi ringkas dan jelas (bersihkan nomor referensi panjang)",
    "type": "Cash In" | "Cash Out",
    "amount": nominal_angka_positif (integer atau float tanpa pemisah ribuan, contoh: 50000 atau 11758422),
    "balance": saldo_setelah_transaksi (float atau null jika tidak ada)
  }
]

Aturan Wajib:
1. Kolom "type":
   - "Cash In" untuk dana masuk (+), transfer masuk, setoran, bunga bank, gaji payroll, kredit (CR).
   - "Cash Out" untuk dana keluar (-), pembayaran, transfer keluar, QRIS, tarik tunai, biaya admin, debet (DB).
2. Kolom "amount": Harus angka murni positif (non-negatif).
3. Kolom "date": Konversi tanggal mutasi ke format ISO "YYYY-MM-DD". Jika hanya ada tanggal dan bulan (DD/MM), gunakan tahun dari periode laporan di header (atau {$currentYear}).
4. PENTING: Jangan masukkan saldo awal, saldo akhir, nomor rekening, atau informasi nasabah sebagai transaksi! Hanya baris mutasi riil.
5. Balas HANYA dengan JSON array valid (dimulai dengan [ dan diakhiri dengan ]), TANPA backtick markdown (tanpa ```json), tanpa kalimat pengantar/penutup apapun.
PROMPT;

        if (empty($this->apiKey)) {
            Log::warning('OpenRouter API key is empty, cannot parse bank statement with AI.');
            return [];
        }

        $truncatedText = substr($statementText, 0, 18000);

        Log::info('Memulai AI parsing untuk mutasi bank PDF', [
            'length' => strlen($truncatedText),
        ]);

        $response = $this->callApi($this->primaryModel, $systemPrompt, $truncatedText);
        if (!$response || isset($response['error'])) {
            Log::warning('AI bank statement primary model failed, switching to fallback.');
            $response = $this->callApi($this->fallbackModel, $systemPrompt, $truncatedText);
        }

        if ($response && !isset($response['error'])) {
            $content = $response['choices'][0]['message']['content'] ?? '';
            $items = $this->cleanAndParseJsonArray($content);
            if (is_array($items) && count($items) > 0) {
                Log::info('AI berhasil mengekstrak transaksi mutasi bank', ['count' => count($items)]);
                return $items;
            }
        }

        return [];
    }

    /**
     * Membersihkan output teks LLM dan mem-parsing JSON Array.
     */
    public function cleanAndParseJsonArray(string $rawContent): array
    {
        $clean = trim($rawContent);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = trim($clean);

        if (preg_match('/(\[\s*\{.*\}\s*\])/s', $clean, $matches)) {
            $clean = $matches[1];
        }

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function callApi(string $model, string $systemPrompt, string $userMessage, array $history = [], float $temperature = 0.1): ?array
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach (array_slice($history, -10) as $turn) {
                if (!empty($turn['role']) && !empty($turn['content']) && in_array($turn['role'], ['user', 'assistant'], true)) {
                    $messages[] = [
                        'role'    => $turn['role'],
                        'content' => (string) $turn['content'],
                    ];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => config('app.url', 'http://localhost:8000'),
                'X-Title'       => 'Finansia App',
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'       => $model,
                'messages'    => $messages,
                'temperature' => $temperature,
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

    /**
     * Generator Future Plan AI: menyusun judul rencana, target tanggal, dan daftar item beserta jadwal Gantt Chart.
     */
    public function generateFuturePlan(string $userMessage): array
    {
        $today = now()->format('Y-m-d');
        $defaultTarget = now()->addMonths(3)->format('Y-m-d');

        $systemPrompt = <<<PROMPT
Anda adalah AI Perencana Keuangan & Proyek (Future Plan Generator) untuk aplikasi Finansia.
Tanggal hari ini: {$today}.

Tugas Anda: Berdasarkan permintaan pengguna, susun satu **Future Plan** yang realistis, terstruktur menjadi 3 sampai 6 item kebutuhan beserta jadwal linimasa (`start_date` dan `end_date`) untuk Gantt Chart!

Aturan Wajib:
1. Kembalikan HANYA JSON valid tanpa markdown backtick (tanpa ```json):
{
  "title": "string (Judul rencana yang jelas, misal: 'Rencana Rakit PC Gaming')",
  "description": "string (Deskripsi singkat 1 kalimat)",
  "target_date": "YYYY-MM-DD (default {$defaultTarget} jika tidak disebut)",
  "items": [
    {
      "title": "string (Nama item/tahap kebutuhan)",
      "quantity": 1,
      "unit_price": integer_nominal,
      "start_date": "YYYY-MM-DD",
      "end_date": "YYYY-MM-DD",
      "notes": "string (opsional)"
    }
  ],
  "reply": "string (pesan pengantar singkat yang ramah)"
}
2. Pastikan `end_date` selalu >= `start_date`, dan total (`quantity * unit_price`) seluruh item mendekati atau sesuai budget yang disebut pengguna (jika ada).
PROMPT;

        if (!empty($this->apiKey)) {
            $response = $this->callApi($this->primaryModel, $systemPrompt, $userMessage, [], 0.3);
            if (!$response || isset($response['error'])) {
                $response = $this->callApi($this->fallbackModel, $systemPrompt, $userMessage, [], 0.3);
            }

            if ($response && !isset($response['error'])) {
                $parsed = $this->cleanAndParseJson($response);
                if (!empty($parsed['title']) && !empty($parsed['items']) && is_array($parsed['items'])) {
                    return $parsed;
                }
            }
        }

        return $this->fallbackFuturePlanGenerator($userMessage);
    }

    /**
     * Fallback lokal cerdas untuk Future Plan Generator jika OpenRouter offline.
     */
    protected function fallbackFuturePlanGenerator(string $userMessage): array
    {
        $lower = strtolower($userMessage);

        // Ekstrak nominal budget (misal 15jt, 100 juta, 500rb)
        $budget = 10_000_000;
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(jt|juta|rb|k|ribu)\b/i', $lower, $m)) {
            $num = (float) str_replace(',', '.', $m[1]);
            $unit = strtolower($m[2]);
            $budget = in_array($unit, ['jt', 'juta'], true) ? (int) round($num * 1_000_000) : (int) round($num * 1_000);
        }

        // Ekstrak durasi bulan jika disebut
        $months = 3;
        if (preg_match('/(\d+)\s*bulan\b/i', $lower, $mm)) {
            $months = max(1, min(36, (int) $mm[1]));
        }

        $cleanTitle = preg_replace('/\b(?:buat(?:kan)?|tolong|saya|aku|mau|ingin|rencana|future\s*plan|budget|anggaran|sebesar|senilai|selama|untuk)\b/i', '', $userMessage);
        $cleanTitle = preg_replace('/\b\d+(?:[.,]\d+)?\s*(?:jt|juta|rb|k|ribu|bulan)\b/i', '', $cleanTitle);
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle), " \t\n\r\0\x0B,.-:");
        $planTitle = !empty($cleanTitle) ? 'Rencana ' . ucwords($cleanTitle) : 'Rencana Masa Depan AI';

        $start = now()->startOfDay();
        $targetDate = $start->copy()->addMonths($months);

        $p1 = (int) round($budget * 0.25);
        $p2 = (int) round($budget * 0.45);
        $p3 = max(0, $budget - $p1 - $p2);

        $totalDays = max(14, $start->diffInDays($targetDate));
        $step1End = $start->copy()->addDays((int) round($totalDays * 0.3));
        $step2Start = $step1End->copy()->addDay();
        $step2End = $start->copy()->addDays((int) round($totalDays * 0.7));
        $step3Start = $step2End->copy()->addDay();

        return [
            'title'       => $planTitle,
            'description' => 'Rencana terstruktur otomatis oleh Finansia AI dengan estimasi budget Rp ' . number_format($budget, 0, ',', '.'),
            'target_date' => $targetDate->format('Y-m-d'),
            'items'       => [
                [
                    'title'      => 'Tahap 1: Persiapan & Riset Kebutuhan',
                    'quantity'   => 1,
                    'unit_price' => $p1,
                    'start_date' => $start->format('Y-m-d'),
                    'end_date'   => $step1End->format('Y-m-d'),
                    'notes'      => 'Survei vendor/harga & DP awal',
                ],
                [
                    'title'      => 'Tahap 2: Pembelian Komponen Utama',
                    'quantity'   => 1,
                    'unit_price' => $p2,
                    'start_date' => $step2Start->format('Y-m-d'),
                    'end_date'   => $step2End->format('Y-m-d'),
                    'notes'      => 'Alokasi dana terbesar',
                ],
                [
                    'title'      => 'Tahap 3: Penyelesaian & Dana Cadangan',
                    'quantity'   => 1,
                    'unit_price' => $p3,
                    'start_date' => $step3Start->format('Y-m-d'),
                    'end_date'   => $targetDate->format('Y-m-d'),
                    'notes'      => 'Pelunasan & biaya tak terduga',
                ],
            ],
            'reply' => "Saya telah menyusun draft **{$planTitle}** lengkap dengan estimasi biaya & jadwal Gantt Chart. Silakan periksa lalu klik **Buat Future Plan**:",
        ];
    }

    /**
     * OCR & Klasifikasi Gambar Keuangan (Screenshot M-Banking, Foto Struk, Foto Slip Gaji).
     * Menolak gambar di luar 3 kategori tersebut atau yang tidak mengandung nominal transaksi.
     */
    public function parseImageReceipt(string $base64DataUri, array $userAccounts = [], array $userCategories = [], string $caption = ''): array
    {
        $accountsList = implode(', ', $userAccounts) ?: 'Tunai, Rekening Utama';
        $categoriesList = implode(', ', $userCategories) ?: 'Makanan & Minuman, Transportasi, Belanja, Tagihan, Gaji, Lainnya';
        $currentDate = now()->format('Y-m-d');

        $systemPrompt = <<<PROMPT
Anda adalah AI Vision OCR & Klasifikasi Dokumen Keuangan untuk aplikasi Finansia.
Tugas Anda:
1. Periksa gambar yang diunggah pengguna. Tentukan apakah gambar tersebut termasuk salah satu dari 3 kategori VALID berikut DAN mengandung nominal uang/angka transaksi yang jelas:
   - "mbanking_screenshot" (Screenshot M-Banking / E-Wallet / QRIS / Bukti Transfer / Mutasi)
   - "receipt" (Foto Struk Belanja / Kasir / Restoran / Nota Pembelian)
   - "payslip" (Foto Slip Gaji / Bukti Pembayaran Penghasilan)
2. Jika gambar BUKAN salah satu dari 3 kategori di atas (misalnya selfie, pemandangan, meme, hewan, foto acak, atau tidak mengandung angka nominal transaksi):
   Kembalikan JSON:
   {
     "is_valid": false,
     "image_category": "other",
     "image_category_label": "Bukan Dokumen Keuangan",
     "items": [],
     "reply": "Maaf, AI tidak dapat memproses gambar ini karena bukan termasuk kategori Screenshot M-Banking, Foto Struk, atau Foto Slip Gaji yang mengandung nominal transaksi."
   }
3. Jika gambar VALID:
   - Tentukan "type": "cash_in" (untuk slip gaji atau transfer masuk) atau "cash_out" (untuk struk belanja, QRIS bayar, atau transfer keluar).
   - Ekstrak nominal total akhir ("amount" sebagai integer positif tanpa titik/koma), deskripsi ringkas ("description", misal nama merchant/toko atau keterangan transfer), tanggal transaksi ("date" format YYYY-MM-DD, default {$currentDate} jika tidak terbaca), rekening paling cocok dari [{$accountsList}], dan kategori paling cocok dari [{$categoriesList}].
   - Kembalikan JSON valid TANPA backtick markdown:
   {
     "is_valid": true,
     "image_category": "mbanking_screenshot" | "receipt" | "payslip",
     "image_category_label": "Screenshot M-Banking" | "Foto Struk" | "Foto Slip Gaji",
     "items": [
       {
         "type": "cash_out" | "cash_in",
         "amount": 125000,
         "account_name": "string",
         "category": "string",
         "description": "string",
         "date": "YYYY-MM-DD"
       }
     ],
     "reply": "string (ringkasan singkat hasil deteksi OCR, misal: 'Terdeteksi Foto Struk Indomaret sebesar Rp 125.000. Silakan konfirmasi detail di bawah ini:')"
   }
PROMPT;

        $userPrompt = !empty($caption)
            ? "Analisa gambar ini. Catatan tambahan pengguna: {$caption}"
            : "Analisa gambar ini dan ekstrak data transaksinya jika termasuk kategori dokumen keuangan.";

        if (empty($this->apiKey)) {
            return [
                'is_valid'             => false,
                'image_category'       => 'other',
                'image_category_label' => 'API Key Belum Tersedia',
                'items'                => [],
                'reply'                => 'Maaf, fitur OCR gambar membutuhkan koneksi OpenRouter Vision API yang aktif.',
            ];
        }

        $response = $this->callVisionApi($this->primaryModel, $systemPrompt, $userPrompt, $base64DataUri);
        if (!$response || isset($response['error'])) {
            Log::warning('OpenRouter Vision primary model failed, switching to fallback.');
            $response = $this->callVisionApi($this->fallbackModel, $systemPrompt, $userPrompt, $base64DataUri);
        }

        if ($response && !isset($response['error'])) {
            $parsed = $this->cleanAndParseJson($response);
            if (!empty($parsed) && array_key_exists('is_valid', $parsed)) {
                return $parsed;
            }
        }

        return [
            'is_valid'             => false,
            'image_category'       => 'other',
            'image_category_label' => 'Tidak Terdeteksi',
            'items'                => [],
            'reply'                => 'AI tidak bisa memproses gambar ini karena tidak terdeteksi sebagai Screenshot M-Banking, Foto Struk, atau Foto Slip Gaji yang valid.',
        ];
    }

    protected function callVisionApi(string $model, string $systemPrompt, string $userText, string $base64DataUri): ?array
    {
        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => config('app.url', 'http://localhost:8000'),
                'X-Title'       => 'Finansia App',
                'Content-Type'  => 'application/json',
            ])->timeout(25)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'    => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    [
                        'role'    => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $userText],
                            ['type' => 'image_url', 'image_url' => ['url' => $base64DataUri]],
                        ],
                    ],
                ],
                'temperature' => 0.1,
            ]);

            if ($res->successful()) {
                return $res->json();
            }

            Log::warning('OpenRouter Vision API returned non-200: ' . $res->status() . ' - ' . $res->body());
        } catch (\Throwable $e) {
            Log::error('OpenRouter Vision call error: ' . $e->getMessage());
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

        // 0. Deteksi Pembuatan Future Plan AI (RECORD_FUTURE_PLAN)
        $isFuturePlanRequest = preg_match('/\b(?:buat(?:kan)?|bikin|susun)\b/i', $lowerMsg)
            && preg_match('/\b(?:future\s*plan|rencana\s+(?!anggaran|budget)|proyek)\b/i', $lowerMsg);

        if ($isFuturePlanRequest) {
            return [
                'intent' => 'RECORD_FUTURE_PLAN',
                'data'   => null,
                'reply'  => 'Pembuatan Future Plan terdeteksi.',
            ];
        }

        // 1. Deteksi Konsultasi Financial Advisor (FINANCIAL_ADVISOR)
        $hasRecordingAction = preg_match('/\b(?:atur|buat|catat(?:kan)?|pasang|tetapkan|tambah(?:kan)?|masukkan|beli|bayar|gaji|masuk)\b/is', $lowerMsg)
            && preg_match('/\d+\s*(?:rb|k|ribu|rebu|jt|juta|000)\b/i', $lowerMsg);

        $isAdvisorQuery = !$hasRecordingAction
            && preg_match('/\b(?:saran|advisor|konsultasi|evaluasi|kesehatan\s+finansial|keuangan|boros|hemat|menabung|nabung|strategi|prioritas|lunasin|hutang|piutang|future\s*plan|rencana\s+masa\s+depan|dana\s+darurat|gimana\s+kondisi|menurutmu|sehat\s+gak|sehat\s+ga|cukup\s+gak|cukup\s+ga)\b/i', $lowerMsg);

        if ($isAdvisorQuery) {
            return [
                'intent' => 'FINANCIAL_ADVISOR',
                'data'   => null,
                'reply'  => 'Konsultasi financial advisor terdeteksi.',
            ];
        }

        // 2. Deteksi Pertanyaan Finansial Spesifik (FINANCIAL_QUERY)
        $isFinancialQuery = preg_match('/\b(?:berapa|habis berapa|sisa|cek|total|kondisi|rekap|ringkasan|sebutkan|daftar|list|top\s*\d*|terbesar|terbanyak|tertinggi|apa\s+aja|apa\s+saja|mana\s+yang|aman gak|aman ga|masih aman)\b/i', $lowerMsg)
            && preg_match('/\b(?:saldo|uang|kas|pengeluaran|pemasukan|transaksi|jajan|makan|kategori|belanja|bulan ini|minggu ini|hari ini|kemarin|anggaran|budget|rekening|tabungan)\b/i', $lowerMsg);

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
        $isBudgetIntent = preg_match('/\b(?:anggaran|budget|plafon|batas(?:\s+pengeluaran)?|alokasi|pos\s+pengeluaran|rencana\s+pengeluaran)\b/i', $lowerMsg);

        if ($isBudgetIntent) {
            $budgetItems = $this->extractMultipleBudgets($message, $userCategories);
            if (!empty($budgetItems)) {
                $targetDate = $this->detectMonthYearFromString($message);
                $indonesianMonths = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $monthName = $indonesianMonths[$targetDate['month']] ?? 'bulan ini';

                return [
                    'intent' => 'RECORD_BUDGET',
                    'data'   => [
                        'items' => $budgetItems,
                    ],
                    'reply'  => count($budgetItems) > 1
                        ? 'Saya menemukan ' . count($budgetItems) . ' rencana anggaran pos untuk ' . $monthName . '. Silakan periksa rincian di bawah ini dan konfirmasi:'
                        : 'Saya telah menyiapkan penetapan anggaran pos Anda untuk ' . $monthName . '. Silakan periksa dan konfirmasi:'
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
     * Memisahkan pesan pengguna menjadi klausa item-item individual secara cerdas.
     * Mendukung pemisah baris baru (\n), tanda bullet (-, *, •, 1., dll),
     * serta pemisah kata/tanda baca (dan, serta, sama, lalu, ;, koma).
     */
    public function splitIntoItemClauses(string $message): array
    {
        $rawLines = preg_split('/[\r\n]+/', $message);
        $clauses = [];

        foreach ($rawLines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Bersihkan bullet di awal baris: "-", "*", "•", "1.", "1)", dsb.
            $line = preg_replace('/^\s*(?:[-*•–—\>]|\d+[\.)])\s*/u', '', $line);
            if (empty($line)) {
                continue;
            }

            // Sub-split jika di dalam baris ada bullet tambahan, titik dua header, atau kata penghubung
            // Perhatikan: koma tidak dipecah jika diapit angka desimal (misal "1,5 juta")
            $subClauses = preg_split('/(?:\s*(?:[-*•–—]|\b\d+[\.)])\s+|:\s+|\s*(?:dan|serta|sama|lalu|;)\s+|\s*,\s*(?![0-9]))/iu', $line);
            foreach ($subClauses as $sub) {
                $sub = trim($sub);
                if (!empty($sub)) {
                    $clauses[] = $sub;
                }
            }
        }

        return $clauses;
    }

    /**
     * Mendeteksi bulan dan tahun dari teks (contoh: "bulan oktober", "november 2026", "bulan depan").
     */
    public function detectMonthYearFromString(string $text): array
    {
        $lower = strtolower($text);
        $month = (int) now()->month;
        $year  = (int) now()->year;

        // Cek relatif: bulan depan / bulan lalu / bulan ini
        if (preg_match('/\bbulan\s+depan\b/i', $lower)) {
            $next = now()->addMonth();
            return ['month' => (int)$next->month, 'year' => (int)$next->year];
        }
        if (preg_match('/\bbulan\s+(?:lalu|kemarin)\b/i', $lower)) {
            $prev = now()->subMonth();
            return ['month' => (int)$prev->month, 'year' => (int)$prev->year];
        }

        $monthMap = [
            1  => ['januari', 'jan'],
            2  => ['februari', 'feb'],
            3  => ['maret', 'mar'],
            4  => ['april', 'apr'],
            5  => ['mei', 'may'],
            6  => ['juni', 'jun'],
            7  => ['juli', 'jul'],
            8  => ['agustus', 'ags', 'agu', 'agust'],
            9  => ['september', 'sep', 'sept'],
            10 => ['oktober', 'okt', 'october', 'oct'],
            11 => ['november', 'nov'],
            12 => ['desember', 'des', 'december', 'dec'],
        ];

        foreach ($monthMap as $mNum => $names) {
            foreach ($names as $name) {
                if (preg_match('/\b' . preg_quote($name, '/') . '\b/i', $lower)) {
                    $month = $mNum;
                    break 2;
                }
            }
        }

        if (preg_match('/\b(20\d{2})\b/', $lower, $yearMatches)) {
            $year = (int) $yearMatches[1];
        }

        return ['month' => $month, 'year' => $year];
    }

    /**
     * Mengekstrak beberapa anggaran kebutuhan sekaligus dari pesan pengguna.
     */
    public function extractMultipleBudgets(string $message, array $userCategories = []): array
    {
        // 1. Deteksi bulan & tahun global dari pesan
        $targetDate = $this->detectMonthYearFromString($message);
        $globalMonth = $targetDate['month'];
        $globalYear  = $targetDate['year'];

        // 2. Pisahkan klausa item
        $clauses = $this->splitIntoItemClauses($message);
        $items = [];

        foreach ($clauses as $clause) {
            $clause = trim($clause);
            if (empty($clause)) {
                continue;
            }

            $amount = $this->extractAmountFromString($clause);
            if ($amount >= 1000) {
                // Periksa apakah klausa ini memiliki override bulan sendiri
                $clauseDate = $this->detectMonthYearFromString($clause);
                $hasClauseMonth = preg_match('/\b(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)\b/i', $clause);
                $itemMonth = $hasClauseMonth ? $clauseDate['month'] : $globalMonth;
                $itemYear  = $hasClauseMonth ? $clauseDate['year'] : $globalYear;

                $category = $this->detectCategoryFromString($clause, 'cash_out', $userCategories);
                $name = $this->extractBudgetNameFromString($clause);
                if (empty($name) || strtolower($name) === 'lainnya') {
                    $name = $category;
                }

                $items[] = [
                    'name'     => $name,
                    'category' => $category,
                    'amount'   => $amount,
                    'month'    => $itemMonth,
                    'year'     => $itemYear,
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
                    'month'    => $globalMonth,
                    'year'     => $globalYear,
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

        // Pisahkan pesan menjadi klausa item
        $clauses = $this->splitIntoItemClauses($message);
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

        // 1. Pola: "1 setengah juta" atau "setengah juta"
        if (preg_match('/(\d+)?\s*setengah\s*(?:jt|juta)\b/i', $lower, $m)) {
            $base = !empty($m[1]) ? (int)$m[1] * 1000000 : 0;
            return $base + 500000;
        }

        // 2. Pola: "2 juta setengah" / "1jt setengah"
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:jt|juta)\s*setengah\b/i', $lower, $m)) {
            $num = (float)str_replace(',', '.', $m[1]);
            return (int) round($num * 1000000) + 500000;
        }

        // 3. Pola: "1 juta 100" / "1juta 100" / "1jt 500" / "1 juta 100rb"
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:jt|juta)\s*(\d{1,3})\s*(?:rb|k|ribu|rebu)?\b/i', $lower, $m)) {
            $juta = (float)str_replace(',', '.', $m[1]);
            $ratusan = (int)$m[2];
            return (int) round($juta * 1000000) + ($ratusan * 1000);
        }

        // 4. Pola Standar: "1.1 juta", "600 rebu", "700k", "600 k", "Rp 50.000", "50rb"
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
            'Keluarga & Sedekah'  => ['pegangan mamah', 'pegangan', 'mamah', 'ibu', 'ayah', 'bapak', 'ortu', 'orang tua', 'keluarga', 'sedekah', 'infaq', 'zakat', 'adik', 'anak', 'istri', 'suami', 'mertua'],
            'Tabungan & Investasi'=> ['nabung', 'tabungan', 'investasi', 'deposito', 'reksadana', 'saham', 'crypto', 'emas'],
            'Pendidikan'          => ['sekolah', 'kuliah', 'kursus', 'les', 'buku', 'spp', 'ujian', 'pendidikan'],
            'Makanan & Minuman'   => ['makan', 'minum', 'kopi', 'ramen', 'bakso', 'soto', 'nasgor', 'nasi', 'resto', 'restoran', 'cafe', 'snack', 'roti', 'ayam', 'sarapan', 'lunch', 'dinner', 'kuliner', 'jajan'],
            'Transportasi'        => ['bensin', 'pertalite', 'pertamax', 'solar', 'gojek', 'grab', 'ojol', 'maxim', 'parkir', 'tol', 'kereta', 'krl', 'mrt', 'angkot', 'bus', 'tiket', 'transport', 'ongkos'],
            'Belanja'             => ['belanja', 'beli', 'indomaret', 'alfamart', 'supermarket', 'mall', 'shopee', 'tokopedia', 'baju', 'kaos', 'sepatu', 'celana', 'shopping', 'sembako', 'uang saku', 'saku', 'pribadi', 'uang saku wahyu'],
            'Utilitas & Tagihan'  => ['listrik', 'pln', 'air', 'pdam', 'wifi', 'indihome', 'biznet', 'internet', 'pulsa', 'kuota', 'paket kuota', 'paket data', 'paket', 'telkomsel', 'tagihan', 'bpjs'],
            'Hiburan'             => ['nonton', 'bioskop', 'xxi', 'cinema', 'game', 'steam', 'top up', 'netflix', 'spotify', 'karaoke', 'liburan', 'jalan-jalan'],
            'Kesehatan'           => ['obat', 'dokter', 'apotek', 'apotik', 'vitamin', 'klinik', 'rumah sakit', 'rs']
        ];

        foreach ($catRules as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/iu', $lower)) {
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
        $clean = preg_replace('/^[-*•–—\d\.\)\>]+\s*/u', '', $clean);
        $clean = preg_replace('/\b(?:atur|buat|catat(?:kan)?|pasang|tetapkan|tambah(?:kan)?|masukkan|tolong|rencana(?:kan)?|alokasi(?:kan)?)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:anggaran|budget|plafon|batas(?:\s+pengeluaran)?|kebutuhan)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:untuk|sebesar|senilai|nominal|sebulan|tiap\s+bulan|per\s+bulan|dong|nih|ya|deh)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:bulan\s+)?(?:januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember|jan|feb|mar|apr|may|jun|jul|ags|agu|agust|sep|sept|okt|oct|nov|des|dec)\b/i', '', $clean);
        $clean = preg_replace('/\b(?:bulan\s+(?:ini|depan|lalu|kemarin))\b/i', '', $clean);
        $clean = preg_replace('/\b(20\d{2})\b/', '', $clean);

        // Strip pola nominal
        $clean = preg_replace('/(\d+)?\s*setengah\s*(?:jt|juta)\b/i', '', $clean);
        $clean = preg_replace('/(\d+(?:[.,]\d+)?)\s*(?:jt|juta)\s*setengah\b/i', '', $clean);
        $clean = preg_replace('/(\d+(?:[.,]\d+)?)\s*(?:jt|juta)\s*(\d{1,3})\s*(?:rb|k|ribu|rebu)?\b/i', '', $clean);
        $clean = preg_replace('/(?:rp\.?\s*)?\d+(?:[.,]\d+)?\s*(?:rb|k|ribu|rebu|jt|juta)?\b/i', '', $clean);

        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        $clean = trim($clean, " \t\n\r\0\x0B,.-_:");

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
            'Keluarga & Sedekah'  => ['pegangan mamah', 'pegangan', 'mamah', 'ibu', 'ayah', 'bapak', 'ortu', 'orang tua', 'keluarga', 'sedekah', 'infaq', 'zakat', 'adik', 'anak', 'istri', 'suami', 'mertua'],
            'Tabungan & Investasi'=> ['nabung', 'tabungan', 'investasi', 'deposito', 'reksadana', 'saham', 'crypto', 'emas'],
            'Pendidikan'          => ['sekolah', 'kuliah', 'kursus', 'les', 'buku', 'spp', 'ujian', 'pendidikan'],
            'Makanan & Minuman'   => ['makan', 'minum', 'kopi', 'ramen', 'bakso', 'soto', 'nasgor', 'nasi', 'resto', 'restoran', 'cafe', 'snack', 'roti', 'ayam', 'sarapan', 'lunch', 'dinner', 'kuliner', 'jajan'],
            'Transportasi'        => ['bensin', 'pertalite', 'pertamax', 'solar', 'gojek', 'grab', 'ojol', 'maxim', 'parkir', 'tol', 'kereta', 'krl', 'mrt', 'angkot', 'bus', 'tiket', 'transport', 'ongkos'],
            'Belanja'             => ['belanja', 'beli', 'indomaret', 'alfamart', 'supermarket', 'mall', 'shopee', 'tokopedia', 'baju', 'kaos', 'sepatu', 'celana', 'shopping', 'sembako', 'uang saku', 'saku', 'pribadi', 'uang saku wahyu'],
            'Utilitas & Tagihan'  => ['listrik', 'pln', 'air', 'pdam', 'wifi', 'indihome', 'biznet', 'internet', 'pulsa', 'kuota', 'paket kuota', 'paket data', 'paket', 'telkomsel', 'tagihan', 'bpjs'],
            'Hiburan'             => ['nonton', 'bioskop', 'xxi', 'cinema', 'game', 'steam', 'top up', 'netflix', 'spotify', 'karaoke', 'liburan', 'jalan-jalan'],
            'Kesehatan'           => ['obat', 'dokter', 'apotek', 'apotik', 'vitamin', 'klinik', 'rumah sakit', 'rs']
        ];

        foreach ($catRules as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/iu', $lower)) {
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
     * Ekstrak parameter query database dinamis dari pertanyaan user (tanpa hardcode angka/periode tetap).
     */
    public function extractDynamicQueryParams(string $userMessage, array $userAccounts = [], array $userCategories = []): ?array
    {
        $lower = strtolower($userMessage);

        $wantsSpecificData = preg_match('/\b(?:top\s*\d+|sebutkan|daftar|list|cari|tampilkan|terbesar|tertinggi|termahal|di\s+atas|lebih\s+dari|bulan\s+lalu|minggu\s+lalu|tahun\s+ini|kemarin|hari\s+ini)\b/i', $lower);
        if (!$wantsSpecificData) {
            return null;
        }

        $params = [
            'type'    => str_contains($lower, 'pemasukan') || str_contains($lower, 'gaji') ? 'Cash In' : 'Cash Out',
            'sort_by' => 'amount_desc',
            'limit'   => 5,
        ];

        // Dynamic limit: "top 3", "top 7", "10 pengeluaran", etc.
        if (preg_match('/\b(?:top|tampilkan|sebutkan)\s+(\d{1,2})\b/i', $lower, $m)) {
            $params['limit'] = max(1, min(20, (int) $m[1]));
        }

        // Dynamic date range
        if (str_contains($lower, 'hari ini')) {
            $params['date_from'] = now()->toDateString();
            $params['date_to'] = now()->toDateString();
        } elseif (str_contains($lower, 'kemarin')) {
            $params['date_from'] = now()->subDay()->toDateString();
            $params['date_to'] = now()->subDay()->toDateString();
        } elseif (str_contains($lower, 'minggu ini')) {
            $params['date_from'] = now()->startOfWeek(Carbon::MONDAY)->toDateString();
            $params['date_to'] = now()->endOfWeek(Carbon::SUNDAY)->toDateString();
        } elseif (str_contains($lower, 'minggu lalu')) {
            $params['date_from'] = now()->subWeek()->startOfWeek(Carbon::MONDAY)->toDateString();
            $params['date_to'] = now()->subWeek()->endOfWeek(Carbon::SUNDAY)->toDateString();
        } elseif (str_contains($lower, 'bulan lalu')) {
            $params['date_from'] = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
            $params['date_to'] = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        } elseif (str_contains($lower, 'tahun ini')) {
            $params['date_from'] = now()->startOfYear()->toDateString();
            $params['date_to'] = now()->endOfYear()->toDateString();
        } else {
            // Default: bulan ini
            $params['date_from'] = now()->startOfMonth()->toDateString();
            $params['date_to'] = now()->endOfMonth()->toDateString();
        }

        // Dynamic account filter
        foreach ($userAccounts as $acc) {
            if (!empty($acc) && str_contains($lower, strtolower($acc))) {
                $params['account'] = $acc;
                break;
            }
        }

        // Dynamic category filter
        foreach ($userCategories as $cat) {
            if (!empty($cat) && str_contains($lower, strtolower($cat))) {
                $params['category'] = $cat;
                break;
            }
        }

        // Dynamic min_amount filter: "di atas 500rb", "lebih dari 1jt"
        if (preg_match('/(?:di\s*atas|lebih\s+dari|>)\s*(?:rp\.?\s*)?(\d+(?:[.,]\d+)?)\s*(rb|k|ribu|jt|juta)?/i', $lower, $am)) {
            $num = (float) str_replace(',', '.', $am[1]);
            $unit = strtolower($am[2] ?? '');
            if (in_array($unit, ['rb', 'k', 'ribu'], true)) $num *= 1000;
            elseif (in_array($unit, ['jt', 'juta'], true)) $num *= 1000000;
            $params['min_amount'] = (int) $num;
        }

        return $params;
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

        // Jika ada hasil query dinamis dari database, tampilkan langsung hasil akuratnya
        if (!empty($snapshot['hasil_query_dinamis'])) {
            $dq = $snapshot['hasil_query_dinamis'];
            $rows = $dq['hasil_daftar'] ?? [];
            $totalMatched = $dq['total_transaksi_cocok'] ?? 0;
            $totalNominal = 'Rp ' . number_format($dq['total_nominal'] ?? 0, 0, ',', '.');

            if (empty($rows)) {
                return "Tidak ditemukan transaksi yang cocok pada rentang waktu/filter tersebut.";
            }

            $listLines = ["📊 **Hasil Data Transaksi ({$totalMatched} transaksi ditemukan, Total: {$totalNominal}):**"];
            foreach ($rows as $idx => $tx) {
                $num = $idx + 1;
                $nom = 'Rp ' . number_format($tx['amount'] ?? 0, 0, ',', '.');
                $cat = $tx['category'] ?? 'Lainnya';
                $acc = $tx['rekening'] ?? '-';
                $tgl = $tx['transaction_date'] ?? '-';
                $listLines[] = "- **#{$num} {$tx['description']}** — **{$nom}** ({$cat} • {$acc} • {$tgl})";
            }

            return implode("\n", $listLines);
        }

        // Pertanyaan Top Pengeluaran / Terbesar (misal: "sebutkan top 5 pengeluaran bulan ini")
        if (preg_match('/\b(?:top|terbesar|tertinggi|sebutkan|daftar|list)\b/i', $lower) && (str_contains($lower, 'pengeluaran') || str_contains($lower, 'belanja') || str_contains($lower, 'transaksi'))) {
            $top5 = $snapshot['top_5_pengeluaran_bulan_ini'] ?? [];
            if (empty($top5)) {
                return "Belum ada catatan pengeluaran untuk {$periode}.";
            }

            $listLines = ["🔥 **Top " . count($top5) . " Pengeluaran Terbesar ({$periode}):**"];
            foreach ($top5 as $idx => $tx) {
                $num = $idx + 1;
                $nom = 'Rp ' . number_format($tx['amount'] ?? 0, 0, ',', '.');
                $cat = $tx['category'] ?? 'Lainnya';
                $tgl = $tx['transaction_date'] ?? '-';
                $listLines[] = "- **#{$num} {$tx['description']}** — **{$nom}** ({$cat} • {$tgl})";
            }

            return implode("\n", $listLines);
        }

        // Pertanyaan Top Pemasukan
        if (preg_match('/\b(?:top|terbesar|tertinggi|sebutkan|daftar|list)\b/i', $lower) && str_contains($lower, 'pemasukan')) {
            $top5In = $snapshot['top_5_pemasukan_bulan_ini'] ?? [];
            if (empty($top5In)) {
                return "Belum ada catatan pemasukan untuk {$periode}.";
            }

            $listLines = ["💰 **Top " . count($top5In) . " Pemasukan Terbesar ({$periode}):**"];
            foreach ($top5In as $idx => $tx) {
                $num = $idx + 1;
                $nom = 'Rp ' . number_format($tx['amount'] ?? 0, 0, ',', '.');
                $tgl = $tx['transaction_date'] ?? '-';
                $listLines[] = "- **#{$num} {$tx['description']}** — **{$nom}** ({$tgl})";
            }

            return implode("\n", $listLines);
        }

        // Pertanyaan pengeluaran minggu ini
        if (str_contains($lower, 'minggu ini') && (str_contains($lower, 'pengeluaran') || str_contains($lower, 'habis'))) {
            $weekly = 'Rp ' . number_format($snapshot['cashflow_bulan_ini']['pengeluaran_minggu_ini'] ?? 0, 0, ',', '.');
            return "Total pengeluaran kamu minggu ini sebesar **{$weekly}**.";
        }

        // Pertanyaan pengeluaran total
        if (str_contains($lower, 'pengeluaran') || str_contains($lower, 'habis berapa')) {
            return "{$periode} total pengeluaranmu sebesar {$pengeluaran}. Total pemasukanmu {$pemasukan}, sehingga sisa kas bulan ini {$sisaKas}.";
        }

        // Pertanyaan spesifik tentang "boros di mana" / "pos terbesar"
        if (str_contains($lower, 'boros') || str_contains($lower, 'menguras')) {
            $categories = $snapshot['pengeluaran_per_kategori'] ?? [];
            $top5 = $snapshot['top_5_pengeluaran_bulan_ini'] ?? [];

            $lines = ["💡 **Analisis Keborosan & Strategi Hemat ({$periode}):**"];
            if (!empty($categories)) {
                $idx = 1;
                foreach (array_slice($categories, 0, 3, true) as $catName => $catVal) {
                    $lines[] = "- **Kategori #{$idx} ({$catName})**: Rp " . number_format($catVal, 0, ',', '.');
                    $idx++;
                }
            }
            if (!empty($top5)) {
                $biggest = $top5[0];
                $lines[] = "- **Transaksi Terbesar**: {$biggest['description']} (**Rp " . number_format($biggest['amount'], 0, ',', '.') . "**)";
            }
            $lines[] = "\n🎯 **Strategi Hemat**: Pasang batas anggaran di menu **Anggaran** pada kategori terbesar di atas, dan sisihkan tabungan di awal gajian sebelum dibelanjakan.";
            return implode("\n", $lines);
        }

        // Analisis & Saran Financial Advisor Komprehensif
        $health = $snapshot['kesehatan_finansial'] ?? [];
        $savingsRate = $health['savings_rate_persen'] ?? 0;
        $runway = $health['rasio_dana_darurat_bulan'] ?? 0;
        $categories = $snapshot['pengeluaran_per_kategori'] ?? [];
        $topCategory = !empty($categories) ? array_key_first($categories) : null;
        $topCatAmount = $topCategory ? 'Rp ' . number_format($categories[$topCategory], 0, ',', '.') : 'Rp 0';

        $plans = $snapshot['rencana_masa_depan'] ?? [];
        $debts = $snapshot['hutang_piutang'] ?? [];
        $totalHutang = (int) ($debts['total_sisa_hutang'] ?? 0);

        $lines = [];
        $lines[] = "🩺 **Evaluasi Finansia Advisor ({$periode})**";
        $lines[] = "- **Total Saldo**: {$totalSaldo} (estimasi dana darurat **{$runway} bulan** pengeluaran)";
        $lines[] = "- **Arus Kas Bulan Ini**: Masuk {$pemasukan} | Keluar {$pengeluaran} | Savings Rate **{$savingsRate}%**";

        if ($topCategory) {
            $lines[] = "- **Pos Pengeluaran Terbesar**: {$topCategory} ({$topCatAmount})";
        }

        if ($totalHutang > 0) {
            $lines[] = "- **Kewajiban Hutang Aktif**: Rp " . number_format($totalHutang, 0, ',', '.') . " — prioritaskan melunasi hutang yang mendekati jatuh tempo terlebih dahulu.";
        }

        if (!empty($plans)) {
            $firstPlan = $plans[0];
            $lines[] = "- **Target Future Plan**: \"{$firstPlan['judul']}\" (sisa kebutuhan Rp " . number_format($firstPlan['sisa_kebutuhan'], 0, ',', '.') . ", progres {$firstPlan['progres_persen']}%).";
        }

        $advice = $savingsRate >= 20
            ? "✅ **Saran**: Savings rate Anda sudah sehat (≥ 20%). Pertahankan dan alokasikan surplus kas ke Sinking Fund Future Plan atau dana darurat!"
            : "⚠️ **Saran**: Savings rate masih di bawah 20%. Coba evaluasi pengeluaran pada pos terbesar ({$topCategory}) dan pasang batas anggaran bulanan agar arus kas tetap positif.";

        $lines[] = "\n{$advice}";

        return implode("\n", $lines);
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
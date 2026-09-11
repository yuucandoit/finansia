<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Services\FinancialContextService;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    /**
     * Menganalisis pesan teks dari user menggunakan AI Service:
     * - Menjawab pertanyaan kondisi finansial (FINANCIAL_QUERY)
     * - Mengekstrak rincian transaksi untuk konfirmasi simpan (RECORD_TRANSACTION)
     * - Menjawab sapaan dan obrolan umum (GENERAL_CHAT)
     */
    public function parse(Request $request, OpenRouterService $aiService, FinancialContextService $contextService)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $user = $request->user() ?? Auth::user();
        $userMessage = $request->input('message');

        // Ambil daftar rekening milik pengguna
        $savings = $user->savings()->get();
        $accountsList = $savings->pluck('name')->toArray();

        // Ambil daftar kategori pengeluaran & pemasukan
        $categories = Category::all();
        $categoriesList = $categories->pluck('name')->toArray();

        // 1. Cek intent & parse menggunakan AI
        $result = $aiService->parseMessage($userMessage, $accountsList, $categoriesList);
        $intent = $result['intent'] ?? 'GENERAL_CHAT';

        // 2. Jika user bertanya tentang kondisi finansial (FINANCIAL_QUERY)
        if ($intent === 'FINANCIAL_QUERY') {
            $snapshot = $contextService->getUserSnapshot($user);
            $aiAnswer = $aiService->answerFinancialQuery($userMessage, $snapshot);

            return response()->json([
                'status' => 'success',
                'intent' => 'FINANCIAL_QUERY',
                'data' => null,
                'reply' => $aiAnswer,
            ]);
        }

        // 3. Jika user ingin mencatat transaksi (RECORD_TRANSACTION)
        if ($intent === 'RECORD_TRANSACTION' && !empty($result['data'])) {
            if ($savings->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'intent' => 'NO_ACCOUNTS',
                    'reply' => 'Anda belum memiliki rekening tabungan yang terdaftar. Silakan tambahkan rekening tabungan Anda terlebih dahulu di menu <strong>Saving</strong>.',
                    'data' => null,
                ]);
            }

            $extracted = $result['data'];

            // Tentukan Tipe Transaksi
            $rawType = strtolower($extracted['type'] ?? 'cash_out');
            $type = ($rawType === 'cash_in' || $rawType === 'pemasukan') ? 'Cash In' : 'Cash Out';
            $typeLabel = $type === 'Cash In' ? 'Pemasukan (Cash In)' : 'Pengeluaran (Cash Out)';

            // Tentukan Nominal
            $amount = abs((float)($extracted['amount'] ?? 0));

            // Cocokkan Rekening Tabungan dengan milik pengguna di database
            $extractedAccName = strtolower(trim($extracted['account_name'] ?? ''));
            $matchedSaving = null;

            foreach ($savings as $saving) {
                $savingNameLower = strtolower($saving->name);
                if (str_contains($extractedAccName, $savingNameLower) || str_contains($savingNameLower, $extractedAccName)) {
                    $matchedSaving = $saving;
                    break;
                }
            }

            // Fallback jika belum cocok: gunakan rekening pertama user
            if (!$matchedSaving) {
                $matchedSaving = $savings->first();
            }

            // Cocokkan Kategori dengan kategori di database
            $extractedCatName = strtolower(trim($extracted['category'] ?? ''));
            $matchedCategory = null;

            foreach ($categories as $category) {
                $catNameLower = strtolower($category->name);
                if (str_contains($extractedCatName, $catNameLower) || str_contains($catNameLower, $extractedCatName)) {
                    $matchedCategory = $category;
                    break;
                }
            }

            if (!$matchedCategory) {
                $matchedCategory = $categories->firstWhere('name', 'Lainnya') ?? $categories->first();
            }

            // Tanggal
            $dateStr = $extracted['date'] ?? now()->format('Y-m-d');
            try {
                $dateObj = Carbon::parse($dateStr);
            } catch (\Exception $e) {
                $dateObj = now();
            }

            $preparedData = [
                'type' => $type,
                'type_label' => $typeLabel,
                'amount' => $amount,
                'amount_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
                'savings_id' => $matchedSaving->id,
                'account_name' => $matchedSaving->name,
                'category_id' => $matchedCategory ? $matchedCategory->id : null,
                'category_name' => $matchedCategory ? $matchedCategory->name : 'Lainnya',
                'description' => $extracted['description'] ?? $userMessage,
                'date' => $dateObj->format('Y-m-d'),
                'date_formatted' => $dateObj->translatedFormat('d M Y'),
                'available_savings' => $savings->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                'available_categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
            ];

            return response()->json([
                'status' => 'success',
                'intent' => 'RECORD_TRANSACTION',
                'reply' => $result['reply'] ?? 'Saya telah menganalisis transaksi Anda. Silakan periksa rincian di bawah ini dan konfirmasi:',
                'data' => $preparedData,
            ]);
        }

        // 4. Respon Percakapan Umum / Bantuan (GENERAL_CHAT)
        return response()->json([
            'status' => 'success',
            'intent' => 'GENERAL_CHAT',
            'reply' => $result['reply'] ?? 'Ada yang bisa saya bantu catat untuk keuangan Anda hari ini?',
            'data' => null,
        ]);
    }

    /**
     * Alias untuk method parse (mendukung method handle)
     */
    public function handle(Request $request, OpenRouterService $aiService, FinancialContextService $contextService)
    {
        return $this->parse($request, $aiService, $contextService);
    }

    /**
     * Menyimpan transaksi yang telah divalidasi dan dikonfirmasi oleh user ke database.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'savings_id' => 'required|exists:savings,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:Cash In,Cash Out,Transfer,Tabungan',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'transaction_date' => 'nullable|date',
        ]);

        $userId = Auth::id();

        // Pastikan rekening yang dipilih milik pengguna yang sedang login
        $saving = Saving::where('user_id', $userId)->findOrFail($validated['savings_id']);

        try {
            DB::transaction(function () use ($userId, $validated, $saving) {
                // Buat record transaksi
                Transaction::create([
                    'user_id' => $userId,
                    'savings_id' => $saving->id,
                    'category_id' => $validated['category_id'] ?? null,
                    'type' => $validated['type'],
                    'description' => $validated['description'],
                    'amount' => $validated['amount'],
                    'transaction_date' => $validated['transaction_date'] ?? now(),
                    'is_manual' => true,
                ]);

                // Update saldo rekening
                if ($validated['type'] === 'Cash In') {
                    $saving->increment('current_balance', $validated['amount']);
                } else {
                    $saving->decrement('current_balance', $validated['amount']);
                }
            });

            $freshBalance = $saving->fresh()->current_balance;

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan ke rekening ' . $saving->name . '!',
                'new_balance' => $freshBalance,
                'formatted_balance' => 'Rp ' . number_format($freshBalance, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
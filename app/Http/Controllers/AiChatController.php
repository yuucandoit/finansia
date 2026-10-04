<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Services\BudgetService;
use App\Services\FinancialContextService;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    /**
     * Menganalisis pesan teks dari user menggunakan AI Service:
     * - Menjawab pertanyaan kondisi finansial & anggaran (FINANCIAL_QUERY)
     * - Mengekstrak rincian transaksi cashflow (RECORD_TRANSACTION) - single atau multiple
     * - Mengekstrak rincian penetapan anggaran bulanan (RECORD_BUDGET) - single atau multiple
     * - Menjawab sapaan dan obrolan umum (GENERAL_CHAT)
     */
    public function parse(Request $request, OpenRouterService $aiService, FinancialContextService $contextService): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user() ?? Auth::user();
        $userMessage = $request->input('message');

        $savings = $user->savings()->get();
        $accountsList = $savings->pluck('name')->toArray();

        $categories = Category::forUser($user->id)->get();
        $categoriesList = $categories->pluck('name')->toArray();

        $result = $aiService->parseMessage($userMessage, $accountsList, $categoriesList);
        $intent = $result['intent'] ?? 'GENERAL_CHAT';

        // 1. Skenario Pertanyaan Finansial & Anggaran
        if ($intent === 'FINANCIAL_QUERY') {
            $snapshot = $contextService->getUserSnapshot($user);
            $aiAnswer = $aiService->answerFinancialQuery($userMessage, $snapshot);

            return response()->json([
                'status' => 'success',
                'intent' => 'FINANCIAL_QUERY',
                'data'   => null,
                'reply'  => $aiAnswer,
            ]);
        }

        // 2. Skenario Penetapan Anggaran Bulanan (RECORD_BUDGET)
        if ($intent === 'RECORD_BUDGET' && !empty($result['data'])) {
            $rawItems = $result['data']['items'] ?? [$result['data']];
            $preparedBudgets = [];
            $indonesianMonths = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            foreach ($rawItems as $item) {
                $amount = abs((float) ($item['amount'] ?? 0));
                if ($amount < 1000) {
                    continue;
                }

                $month = (int) ($item['month'] ?? now()->month);
                $year  = (int) ($item['year'] ?? now()->year);
                if ($month < 1 || $month > 12) $month = (int) now()->month;
                if ($year < 2020 || $year > 2099) $year = (int) now()->year;

                // Tentukan nama pos kebutuhan spesifik
                $budgetName = trim($item['name'] ?? $item['kebutuhan'] ?? $item['title'] ?? '');
                $extractedCatName = trim($item['category'] ?? '');

                // Resolve kategori master menggunakan BudgetService
                $budgetService = app(\App\Services\BudgetService::class);
                $matchedCategory = $budgetService->resolveCategory($extractedCatName ?: $budgetName, $user->id);

                if (!$matchedCategory) {
                    $matchedCategory = $categories->firstWhere('type', 'Cash Out') ?? $categories->first();
                }

                if ($budgetName === '') {
                    $budgetName = $extractedCatName ?: ($matchedCategory ? $matchedCategory->name : 'Anggaran');
                }

                $preparedBudgets[] = [
                    'name'             => $budgetName,
                    'category_id'      => $matchedCategory ? $matchedCategory->id : null,
                    'category_name'    => $matchedCategory ? $matchedCategory->name : 'Lainnya',
                    'amount'           => $amount,
                    'amount_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'month'            => $month,
                    'year'             => $year,
                    'month_name'       => $indonesianMonths[$month] ?? Carbon::createFromDate($year, $month, 1)->format('F'),
                ];
            }

            if (!empty($preparedBudgets)) {
                $totalAmount = collect($preparedBudgets)->sum('amount');
                $isMultiple = count($preparedBudgets) > 1;

                return response()->json([
                    'status'      => 'success',
                    'intent'      => 'RECORD_BUDGET',
                    'is_multiple' => $isMultiple,
                    'reply'       => $result['reply'] ?? ($isMultiple
                        ? "Saya telah menyiapkan draft penetapan " . count($preparedBudgets) . " anggaran. Silakan periksa dan konfirmasi:"
                        : "Saya telah menyiapkan draft anggaran bulanan Anda. Silakan periksa dan konfirmasi:"),
                    'data'        => [
                        'items'                  => $preparedBudgets,
                        'total_amount'           => $totalAmount,
                        'total_amount_formatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                        'available_categories'   => $categories->where('type', 'Cash Out')->values()->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                    ],
                ]);
            }
        }

        // 3. Skenario Pencatatan Transaksi Cashflow (RECORD_TRANSACTION) - Mendukung Single & Multiple
        if ($intent === 'RECORD_TRANSACTION' && !empty($result['data'])) {
            if ($savings->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'intent' => 'NO_ACCOUNTS',
                    'reply'  => 'Anda belum memiliki rekening tabungan yang terdaftar. Silakan tambahkan rekening tabungan Anda terlebih dahulu di menu <strong>Saving</strong>.',
                    'data'   => null,
                ]);
            }

            $rawItems = $result['data']['items'] ?? [$result['data']];
            $preparedTransactions = [];

            foreach ($rawItems as $extracted) {
                $amount = abs((float) ($extracted['amount'] ?? 0));
                if ($amount <= 0) {
                    continue;
                }

                $rawType = strtolower($extracted['type'] ?? 'cash_out');
                $type = ($rawType === 'cash_in' || $rawType === 'pemasukan') ? 'Cash In' : 'Cash Out';
                $typeLabel = $type === 'Cash In' ? 'Pemasukan (Cash In)' : 'Pengeluaran (Cash Out)';

                // Cocokkan Rekening
                $extractedAccName = strtolower(trim($extracted['account_name'] ?? ''));
                $matchedSaving = null;

                foreach ($savings as $saving) {
                    $savingNameLower = strtolower($saving->name);
                    if (
                        str_contains($extractedAccName, $savingNameLower) ||
                        str_contains($savingNameLower, $extractedAccName) ||
                        ($extractedAccName === 'cash' && $saving->account_number === 'CASH') ||
                        ($extractedAccName === 'tunai' && $saving->account_number === 'CASH')
                    ) {
                        $matchedSaving = $saving;
                        break;
                    }
                }

                if (!$matchedSaving) {
                    $matchedSaving = $savings->firstWhere('account_number', 'CASH') ?? $savings->first();
                }

                // Cocokkan Kategori
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

                $dateStr = $extracted['date'] ?? now()->format('Y-m-d');
                try {
                    $dateObj = Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    $dateObj = now();
                }

                $preparedTransactions[] = [
                    'type'                     => $type,
                    'type_label'               => $typeLabel,
                    'amount'                   => $amount,
                    'amount_formatted'         => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'savings_id'               => $matchedSaving->id,
                    'account_name'             => $matchedSaving->name,
                    'saving_balance'           => (float) $matchedSaving->current_balance,
                    'saving_balance_formatted' => 'Rp ' . number_format($matchedSaving->current_balance, 0, ',', '.'),
                    'category_id'              => $matchedCategory ? $matchedCategory->id : null,
                    'category_name'            => $matchedCategory ? $matchedCategory->name : 'Lainnya',
                    'description'              => $extracted['description'] ?? 'Transaksi AI',
                    'date'                     => $dateObj->format('Y-m-d'),
                    'date_formatted'           => $dateObj->translatedFormat('d M Y'),
                ];
            }

            if (!empty($preparedTransactions)) {
                $totalAmount = collect($preparedTransactions)->sum('amount');
                $isMultiple = count($preparedTransactions) > 1;

                return response()->json([
                    'status'      => 'success',
                    'intent'      => 'RECORD_TRANSACTION',
                    'is_multiple' => $isMultiple,
                    'reply'       => $result['reply'] ?? ($isMultiple
                        ? "Saya telah menganalisis " . count($preparedTransactions) . " transaksi Anda. Silakan periksa rincian di bawah ini dan konfirmasi:"
                        : "Saya telah menganalisis transaksi Anda. Silakan periksa rincian di bawah ini dan konfirmasi:"),
                    'data'        => [
                        'items'                  => $preparedTransactions,
                        'total_amount'           => $totalAmount,
                        'total_amount_formatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                        'available_savings'      => $savings->map(fn($s) => [
                            'id'                => $s->id,
                            'name'              => $s->name,
                            'balance'           => (float) $s->current_balance,
                            'balance_formatted' => 'Rp ' . number_format($s->current_balance, 0, ',', '.'),
                        ]),
                        'available_categories'   => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                    ],
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'intent' => 'GENERAL_CHAT',
            'reply'  => $result['reply'] ?? 'Ada yang bisa saya bantu catat untuk transaksi atau anggaran keuangan Anda hari ini?',
            'data'   => null,
        ]);
    }

    /**
     * Alias untuk parse.
     */
    public function handle(Request $request, OpenRouterService $aiService, FinancialContextService $contextService)
    {
        return $this->parse($request, $aiService, $contextService);
    }

    /**
     * Menyimpan transaksi (single atau multiple) yang telah dikonfirmasi oleh user ke database.
     */
    public function save(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? $request->user()->id;

        // Cek jika request adalah multiple transactions (array 'items')
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            if (empty($items)) {
                return response()->json(['status' => 'error', 'message' => 'Daftar transaksi tidak boleh kosong.'], 422);
            }

            try {
                $savedCount = 0;
                DB::transaction(function () use ($userId, $items, &$savedCount) {
                    foreach ($items as $item) {
                        $saving = Saving::where('user_id', $userId)->findOrFail($item['savings_id']);
                        $amount = (float) $item['amount'];

                        if ($item['type'] !== 'Cash In' && (float) $saving->current_balance < $amount) {
                            throw new \Exception("Saldo rekening {$saving->name} tidak mencukupi untuk transaksi {$item['description']}.");
                        }

                        $txDate = $item['transaction_date'] ?? now();
                        $categoryId = $item['category_id'] ?? null;
                        $budgetItemId = $item['budget_item_id'] ?? null;

                        if (!$budgetItemId && ($item['type'] ?? 'Cash Out') === 'Cash Out' && $categoryId) {
                            $tDate = Carbon::parse($txDate);
                            $b = \App\Models\Budget::where('user_id', $userId)->where('month', $tDate->month)->where('year', $tDate->year)->first();
                            if ($b) {
                                $cands = \App\Models\BudgetItem::where('budget_id', $b->id)->where('category_id', $categoryId)->get();
                                if ($cands->count() === 1) {
                                    $budgetItemId = $cands->first()->id;
                                } elseif ($cands->count() > 1 && !empty($item['description'])) {
                                    $dl = strtolower($item['description']);
                                    foreach ($cands as $cand) {
                                        if (str_contains($dl, strtolower($cand->name))) {
                                            $budgetItemId = $cand->id;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        Transaction::create([
                            'user_id'          => $userId,
                            'savings_id'       => $saving->id,
                            'category_id'      => $categoryId,
                            'budget_item_id'   => $budgetItemId,
                            'type'             => $item['type'] ?? 'Cash Out',
                            'description'      => $item['description'] ?? 'Transaksi AI',
                            'amount'           => $amount,
                            'transaction_date' => $txDate,
                            'is_manual'        => true,
                        ]);

                        if ($item['type'] === 'Cash In') {
                            $saving->increment('current_balance', $amount);
                        } else {
                            $saving->decrement('current_balance', $amount);
                        }

                        $savedCount++;
                    }
                });

                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil menyimpan {$savedCount} transaksi sekaligus ke database!",
                    'count'   => $savedCount,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        // Single transaction save (backward compatible)
        $validated = $request->validate([
            'savings_id'       => 'required|exists:savings,id',
            'category_id'      => 'nullable|exists:categories,id',
            'budget_item_id'   => 'nullable|exists:budget_items,id',
            'type'             => 'required|in:Cash In,Cash Out,Transfer,Tabungan',
            'amount'           => 'required|numeric|min:1',
            'description'      => 'required|string|max:255',
            'transaction_date' => 'nullable|date',
        ]);

        $saving = Saving::where('user_id', $userId)->findOrFail($validated['savings_id']);

        if ($validated['type'] !== 'Cash In' && (float)$saving->current_balance < (float)$validated['amount']) {
            return response()->json([
                'status'  => 'error',
                'message' => "Saldo rekening {$saving->name} tidak mencukupi (Sisa: Rp " . number_format($saving->current_balance, 0, ',', '.') . "). Transaksi ditolak untuk mencegah saldo minus.",
            ], 422);
        }

        $txDate = $validated['transaction_date'] ?? now();
        $categoryId = $validated['category_id'] ?? null;
        $budgetItemId = $validated['budget_item_id'] ?? null;

        if (!$budgetItemId && $validated['type'] === 'Cash Out' && $categoryId) {
            $tDate = Carbon::parse($txDate);
            $b = \App\Models\Budget::where('user_id', $userId)->where('month', $tDate->month)->where('year', $tDate->year)->first();
            if ($b) {
                $cands = \App\Models\BudgetItem::where('budget_id', $b->id)->where('category_id', $categoryId)->get();
                if ($cands->count() === 1) {
                    $budgetItemId = $cands->first()->id;
                } elseif ($cands->count() > 1 && !empty($validated['description'])) {
                    $dl = strtolower($validated['description']);
                    foreach ($cands as $cand) {
                        if (str_contains($dl, strtolower($cand->name))) {
                            $budgetItemId = $cand->id;
                            break;
                        }
                    }
                }
            }
        }

        try {
            DB::transaction(function () use ($userId, $validated, $saving, $categoryId, $budgetItemId, $txDate) {
                Transaction::create([
                    'user_id'          => $userId,
                    'savings_id'       => $saving->id,
                    'category_id'      => $categoryId,
                    'budget_item_id'   => $budgetItemId,
                    'type'             => $validated['type'],
                    'description'      => $validated['description'],
                    'amount'           => $validated['amount'],
                    'transaction_date' => $txDate,
                    'is_manual'        => true,
                ]);

                if ($validated['type'] === 'Cash In') {
                    $saving->increment('current_balance', $validated['amount']);
                } else {
                    $saving->decrement('current_balance', $validated['amount']);
                }
            });

            $freshBalance = $saving->fresh()->current_balance;

            return response()->json([
                'status'            => 'success',
                'message'           => 'Transaksi berhasil disimpan ke rekening ' . $saving->name . '!',
                'new_balance'       => $freshBalance,
                'formatted_balance' => 'Rp ' . number_format($freshBalance, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menyimpan penetapan anggaran (single atau multiple) yang dikonfirmasi oleh user via AI.
     */
    public function saveBudget(Request $request, BudgetService $budgetService): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        // Cek jika request multiple budgets ('items')
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            try {
                $result = $budgetService->recordMultipleBudgets($user, $items);
                return response()->json([
                    'status'  => 'success',
                    'message' => "Berhasil menetapkan {$result['count']} anggaran kategori!",
                    'data'    => $result,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan anggaran: ' . $e->getMessage(),
                ], 422);
            }
        }

        // Single budget save
        $validated = $request->validate([
            'name'        => 'nullable|string|max:100',
            'category_id' => 'nullable',
            'category'    => 'nullable|string',
            'kebutuhan'   => 'nullable|string',
            'amount'      => 'required|numeric|min:1000',
            'month'       => 'nullable|integer|between:1,12',
            'year'        => 'nullable|integer|between:2020,2099',
        ]);

        $name = trim($validated['name'] ?? $validated['kebutuhan'] ?? '');
        $catIdentifier = $validated['category_id'] ?? $validated['category'] ?? null;
        if (!$name && !$catIdentifier) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nama kebutuhan atau kategori wajib diisi.',
            ], 422);
        }

        try {
            $budget = $budgetService->recordBudget(
                $user,
                $catIdentifier ?: $name,
                (float) $validated['amount'],
                $validated['month'] ?? null,
                $validated['year'] ?? null,
                $name ?: null
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Anggaran {$budget->name} ({$budget->category->name}) sebesar Rp " . number_format($budget->amount, 0, ',', '.') . " berhasil disimpan!",
                'data'    => $budget,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan anggaran: ' . $e->getMessage(),
            ], 422);
        }
    }
}
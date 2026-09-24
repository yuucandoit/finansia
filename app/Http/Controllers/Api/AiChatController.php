<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Services\BudgetService;
use App\Services\FinancialContextService;
use App\Services\OpenRouterService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    use ApiResponse;

    /**
     * Menganalisis pesan teks dari user mobile menggunakan AI atau fallback Bahasa Indonesia:
     * - Menjawab pertanyaan finansial & status anggaran (FINANCIAL_QUERY)
     * - Mengekstrak rencana anggaran bulanan (RECORD_BUDGET) - single atau multiple
     * - Mengekstrak transaksi pengeluaran/pemasukan (RECORD_TRANSACTION) - single atau multiple
     * - Obrolan umum (GENERAL_CHAT)
     */
    public function parse(Request $request, OpenRouterService $aiService, FinancialContextService $contextService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $userMessage = trim($validated['message']);

        $savings = Saving::where('user_id', $user->id)->get();
        $categories = Category::all();

        $accountNames = $savings->pluck('name')->toArray();
        $categoryNames = $categories->pluck('name')->toArray();

        $result = $aiService->parseMessage($userMessage, $accountNames, $categoryNames);
        $intent = $result['intent'] ?? 'GENERAL_CHAT';

        // 1. Skenario Pertanyaan Finansial
        if ($intent === 'FINANCIAL_QUERY') {
            $snapshot = $contextService->getUserSnapshot($user);
            $aiAnswer = $aiService->answerFinancialQuery($userMessage, $snapshot);

            return $this->successResponse([
                'intent' => 'FINANCIAL_QUERY',
                'reply'  => $aiAnswer,
                'data'   => null,
            ], 'Pertanyaan keuangan dijawab.');
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
                if ($amount < 1000) continue;

                $month = (int) ($item['month'] ?? now()->month);
                $year  = (int) ($item['year'] ?? now()->year);
                if ($month < 1 || $month > 12) $month = (int) now()->month;
                if ($year < 2020 || $year > 2099) $year = (int) now()->year;

                $extractedCatName = strtolower(trim($item['category'] ?? ''));
                $matchedCategory = null;

                foreach ($categories as $category) {
                    $catNameLower = strtolower($category->name);
                    if (str_contains($extractedCatName, $catNameLower) || str_contains($catNameLower, $extractedCatName)) {
                        $matchedCategory = $category;
                        break;
                    }
                }

                if (!$matchedCategory) {
                    $matchedCategory = $categories->firstWhere('type', 'Cash Out') ?? $categories->first();
                }

                $preparedBudgets[] = [
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

                return $this->successResponse([
                    'intent'                 => 'RECORD_BUDGET',
                    'is_multiple'            => $isMultiple,
                    'reply'                  => $result['reply'] ?? ($isMultiple
                        ? "Saya menemukan " . count($preparedBudgets) . " rencana anggaran kategori. Silakan tinjau dan konfirmasi:"
                        : "Saya telah menyiapkan draft anggaran bulanan Anda. Silakan tinjau dan konfirmasi:"),
                    'items'                  => $preparedBudgets,
                    'total_amount'           => $totalAmount,
                    'total_amount_formatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                ], 'Draft anggaran kategori berhasil dipersiapkan.');
            }
        }

        // 3. Skenario Pencatatan Transaksi Cashflow (RECORD_TRANSACTION)
        if ($intent === 'RECORD_TRANSACTION' && !empty($result['data'])) {
            $rawItems = $result['data']['items'] ?? [$result['data']];
            $preparedTransactions = [];

            foreach ($rawItems as $extracted) {
                $amount = abs((int) ($extracted['amount'] ?? 0));
                if ($amount <= 0) continue;

                $rawType = strtolower($extracted['type'] ?? 'cash_out');
                $type = match ($rawType) {
                    'cash_in', 'pemasukan', 'income' => 'Cash In',
                    'transfer' => 'Transfer',
                    'tabungan' => 'Tabungan',
                    default => 'Cash Out',
                };

                // Cocokkan Rekening Tabungan
                $extractedAccount = strtolower(trim($extracted['account_name'] ?? ''));
                $matchedSaving = null;

                foreach ($savings as $saving) {
                    $savingNameLower = strtolower($saving->name);
                    if (
                        str_contains($savingNameLower, $extractedAccount) ||
                        str_contains($extractedAccount, $savingNameLower) ||
                        ($extractedAccount === 'cash' && $saving->account_number === 'CASH') ||
                        ($extractedAccount === 'tunai' && $saving->account_number === 'CASH')
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

                $preparedTransactions[] = [
                    'type'             => $type,
                    'amount'           => $amount,
                    'amount_formatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'account_id'       => $matchedSaving?->id,
                    'account_name'     => $matchedSaving?->name ?? 'Tanpa Rekening',
                    'category_id'      => $matchedCategory?->id,
                    'category_name'    => $matchedCategory?->name ?? 'Lainnya',
                    'description'      => $extracted['description'] ?? 'Transaksi AI',
                    'date'             => $extracted['date'] ?? now()->format('Y-m-d'),
                ];
            }

            if (!empty($preparedTransactions)) {
                $totalAmount = collect($preparedTransactions)->sum('amount');
                $isMultiple = count($preparedTransactions) > 1;

                return $this->successResponse([
                    'intent'                 => 'RECORD_TRANSACTION',
                    'is_multiple'            => $isMultiple,
                    'reply'                  => $result['reply'] ?? ($isMultiple
                        ? "Saya menganalisis " . count($preparedTransactions) . " transaksi dalam pesan Anda. Silakan tinjau dan konfirmasi:"
                        : "Saya telah menganalisis transaksi Anda. Silakan tinjau dan konfirmasi:"),
                    'items'                  => $preparedTransactions,
                    'total_amount'           => $totalAmount,
                    'total_amount_formatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                ], 'Draft transaksi AI berhasil dianalisis.');
            }
        }

        return $this->successResponse([
            'intent' => 'GENERAL_CHAT',
            'reply'  => $result['reply'] ?? 'Halo! Ada yang bisa saya bantu catat untuk transaksi atau anggaran keuangan Anda hari ini?',
            'data'   => null,
        ], 'Pesan umum.');
    }

    /**
     * Menyimpan transaksi AI (single atau multiple) yang telah dikonfirmasi oleh pengguna ke database.
     */
    public function save(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Mendukung multiple transaksi ('items')
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            if (empty($items)) {
                return $this->errorResponse('Daftar transaksi tidak boleh kosong.', 422);
            }

            try {
                $savedCount = 0;
                DB::transaction(function () use ($userId, $items, &$savedCount) {
                    foreach ($items as $item) {
                        $saving = Saving::where('user_id', $userId)->findOrFail($item['savings_id'] ?? $item['account_id']);
                        $amount = (float) $item['amount'];

                        if (($item['type'] ?? 'Cash Out') !== 'Cash In' && (float) $saving->current_balance < $amount) {
                            throw new \Exception("Saldo rekening {$saving->name} tidak mencukupi untuk transaksi {$item['description']}.");
                        }

                        Transaction::create([
                            'user_id'          => $userId,
                            'savings_id'       => $saving->id,
                            'category_id'      => $item['category_id'] ?? null,
                            'type'             => $item['type'] ?? 'Cash Out',
                            'description'      => $item['description'] ?? 'Transaksi AI',
                            'amount'           => $amount,
                            'transaction_date' => $item['transaction_date'] ?? $item['date'] ?? now(),
                            'is_manual'        => true,
                        ]);

                        if (($item['type'] ?? 'Cash Out') === 'Cash In') {
                            $saving->increment('current_balance', $amount);
                        } else {
                            $saving->decrement('current_balance', $amount);
                        }

                        $savedCount++;
                    }
                });

                return $this->successResponse([
                    'saved_count' => $savedCount,
                ], "Berhasil menyimpan {$savedCount} transaksi sekaligus!");
            } catch (\Exception $e) {
                return $this->errorResponse($e->getMessage(), 422);
            }
        }

        // Single transaction save
        $validated = $request->validate([
            'savings_id'       => ['required', 'exists:savings,id'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'type'             => ['required', 'in:Cash In,Cash Out,Transfer,Tabungan'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'description'      => ['required', 'string', 'max:255'],
            'transaction_date' => ['nullable', 'date'],
        ]);

        $saving = Saving::where('user_id', $userId)->findOrFail($validated['savings_id']);
        $amount = (float) $validated['amount'];

        // Proteksi saldo minus
        if ($validated['type'] !== 'Cash In' && (float) $saving->current_balance < $amount) {
            return $this->errorResponse(
                "Saldo rekening {$saving->name} tidak mencukupi (Sisa: Rp " . number_format($saving->current_balance, 0, ',', '.') . ", Dibutuhkan: Rp " . number_format($amount, 0, ',', '.') . "). Transaksi ditolak untuk mencegah saldo minus.",
                422
            );
        }

        try {
            DB::transaction(function () use ($userId, $validated, $saving, $amount) {
                Transaction::create([
                    'user_id'          => $userId,
                    'savings_id'       => $saving->id,
                    'category_id'      => $validated['category_id'] ?? null,
                    'type'             => $validated['type'],
                    'description'      => $validated['description'],
                    'amount'           => $amount,
                    'transaction_date' => $validated['transaction_date'] ?? now(),
                    'is_manual'        => true,
                ]);

                if ($validated['type'] === 'Cash In') {
                    $saving->increment('current_balance', $amount);
                } else {
                    $saving->decrement('current_balance', $amount);
                }
            });

            $freshBalance = (float) $saving->fresh()->current_balance;

            return $this->successResponse([
                'new_balance'           => $freshBalance,
                'new_balance_formatted' => 'Rp ' . number_format($freshBalance, 0, ',', '.'),
            ], "Transaksi berhasil disimpan ke rekening {$saving->name}!");

        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menyimpan transaksi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Menyimpan penetapan anggaran AI (single atau multiple) yang dikonfirmasi pengguna mobile.
     */
    public function saveBudget(Request $request, BudgetService $budgetService): JsonResponse
    {
        $user = $request->user();

        // Mendukung multiple anggaran ('items')
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            try {
                $result = $budgetService->recordMultipleBudgets($user, $items);
                return $this->successResponse($result, "Berhasil menetapkan {$result['count']} anggaran kategori!");
            } catch (\Exception $e) {
                return $this->errorResponse('Gagal menyimpan anggaran: ' . $e->getMessage(), 422);
            }
        }

        // Single budget save
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount'      => ['required', 'numeric', 'min:1000'],
            'month'       => ['nullable', 'integer', 'between:1,12'],
            'year'        => ['nullable', 'integer', 'between:2020,2099'],
        ]);

        try {
            $budget = $budgetService->recordBudget(
                $user,
                $validated['category_id'],
                (float) $validated['amount'],
                $validated['month'] ?? null,
                $validated['year'] ?? null
            );

            return $this->successResponse([
                'id'               => $budget->id,
                'category_id'      => $budget->category_id,
                'category_name'    => $budget->category->name,
                'amount'           => (float) $budget->amount,
                'amount_formatted' => 'Rp ' . number_format($budget->amount, 0, ',', '.'),
                'month'            => $budget->month,
                'year'             => $budget->year,
            ], "Anggaran kategori {$budget->category->name} berhasil disimpan.");
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menyimpan anggaran: ' . $e->getMessage(), 422);
        }
    }
}

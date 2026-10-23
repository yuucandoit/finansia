<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Services\PdfImportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan daftar transaksi dengan filter dan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = (int) $request->query('per_page', 15);

        $query = Transaction::with(['category', 'savingAccount', 'budgetItem'])
            ->where('user_id', $userId);

        // Filter: Tipe Transaksi
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        // Filter: Pencarian Deskripsi
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('description', 'like', "%{$search}%");
        }

        // Filter: Rekening Tertentu
        if ($request->filled('savings_id')) {
            $query->where('savings_id', $request->query('savings_id'));
        }

        // Filter: Kategori Tertentu
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter: Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [
                Carbon::parse($request->query('start_date'))->startOfDay(),
                Carbon::parse($request->query('end_date'))->endOfDay(),
            ]);
        }

        // Hitung total ringkasan dari query yang difilter
        $totalCashIn = (float) (clone $query)->where('type', 'Cash In')->sum('amount');
        $totalCashOut = (float) (clone $query)->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])->sum('amount');

        $paginated = $query->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate($perPage);

        $items = collect($paginated->items())->map(function ($item) {
            return [
                'id'                      => $item->id,
                'type'                    => $item->type,
                'amount'                  => (float) $item->amount,
                'amount_formatted'        => 'Rp ' . number_format($item->amount, 0, ',', '.'),
                'fee'                     => $item->fee ? (float) $item->fee : null,
                'fee_formatted'           => $item->fee ? 'Rp ' . number_format($item->fee, 0, ',', '.') : null,
                'description'             => $item->description,
                'transaction_date'        => $item->transaction_date ? Carbon::parse($item->transaction_date)->toIso8601String() : null,
                'transaction_date_formatted' => $item->transaction_date ? Carbon::parse($item->transaction_date)->translatedFormat('d M Y, H:i') : null,
                'is_manual'               => (bool) $item->is_manual,
                'category'                => $item->category ? [
                    'id'    => $item->category->id,
                    'name'  => $item->category->name,
                    'color' => $item->category->color,
                ] : null,
                'budget_item'             => $item->budgetItem ? [
                    'id'     => $item->budgetItem->id,
                    'name'   => $item->budgetItem->name,
                    'amount' => (float) $item->budgetItem->amount,
                ] : null,
                'saving_account'          => $item->savingAccount ? [
                    'id'   => $item->savingAccount->id,
                    'name' => $item->savingAccount->name,
                ] : null,
            ];
        });

        return $this->successResponse([
            'summary' => [
                'total_cash_in'           => $totalCashIn,
                'total_cash_in_formatted' => 'Rp ' . number_format($totalCashIn, 0, ',', '.'),
                'total_cash_out'          => $totalCashOut,
                'total_cash_out_formatted'=> 'Rp ' . number_format($totalCashOut, 0, ',', '.'),
            ],
            'transactions' => $items,
            'pagination'   => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ], 'Daftar transaksi berhasil diambil.');
    }

    /**
     * Menyimpan transaksi baru (dengan validasi saldo minus / anti-overdraft).
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'description'            => ['required', 'string', 'max:255'],
            'type'                   => ['required', 'in:Cash In,Cash Out,Transfer,Tabungan'],
            'amount'                 => ['required', 'numeric', 'min:1'],
            'savings_id'             => ['required', 'exists:savings,id'],
            'destination_savings_id' => ['nullable', 'exists:savings,id'],
            'category_id'            => ['nullable', 'exists:categories,id'],
            'budget_item_id'         => ['nullable', 'exists:budget_items,id'],
            'fee'                    => ['nullable', 'numeric', 'min:0'],
            'transaction_date'       => ['nullable', 'date'],
        ]);

        $amount = (float) $validated['amount'];
        $fee = (float) ($validated['fee'] ?? 0);
        $date = $validated['transaction_date'] ?? now();
        $categoryId = $validated['category_id'] ?? null;
        $budgetItemId = !empty($validated['budget_item_id']) ? (int) $validated['budget_item_id'] : null;

        if ($budgetItemId && !$categoryId) {
            $bItem = \App\Models\BudgetItem::find($budgetItemId);
            if ($bItem && $bItem->category_id) {
                $categoryId = $bItem->category_id;
            }
        }

        // Auto-assign ke pos anggaran jika pengeluaran dan belum ada budget_item_id
        if (!$budgetItemId && $validated['type'] === 'Cash Out' && $categoryId) {
            $tDate = Carbon::parse($date);
            $activeBudget = \App\Models\Budget::where('user_id', $userId)
                ->where('month', $tDate->month)
                ->where('year', $tDate->year)
                ->first();

            if ($activeBudget) {
                $candidateItems = \App\Models\BudgetItem::where('budget_id', $activeBudget->id)
                    ->where('category_id', $categoryId)
                    ->get();

                if ($candidateItems->count() === 1) {
                    $budgetItemId = $candidateItems->first()->id;
                } elseif ($candidateItems->count() > 1 && !empty($validated['description'])) {
                    $descLower = strtolower($validated['description']);
                    foreach ($candidateItems as $cItem) {
                        if (str_contains($descLower, strtolower($cItem->name))) {
                            $budgetItemId = $cItem->id;
                            break;
                        }
                    }
                }
            }
        }

        try {
            $transaction = DB::transaction(function () use ($userId, $validated, $amount, $fee, $date, $categoryId, $budgetItemId) {

                // Skenario 1: Transfer atau Tabungan ke rekening tujuan
                if ($validated['type'] === 'Transfer' || ($validated['type'] === 'Tabungan' && !empty($validated['destination_savings_id']))) {
                    $sourceSaving = Saving::where('user_id', $userId)->findOrFail($validated['savings_id']);
                    $destSaving = Saving::where('user_id', $userId)->findOrFail($validated['destination_savings_id']);

                    // Validasi perlindungan saldo minus
                    if ((float) $sourceSaving->current_balance < ($amount + $fee)) {
                        throw new \Exception(
                            "Saldo rekening {$sourceSaving->name} tidak mencukupi (Sisa: Rp " . number_format($sourceSaving->current_balance, 0, ',', '.') . ", Dibutuhkan: Rp " . number_format($amount + $fee, 0, ',', '.') . ")."
                        );
                    }

                    $transferGroupId = (string) \Illuminate\Support\Str::uuid();

                    // 1. Catat transaksi keluar di rekening asal
                    $txOut = Transaction::create([
                        'user_id'          => $userId,
                        'savings_id'       => $sourceSaving->id,
                        'category_id'      => $categoryId,
                        'budget_item_id'   => $budgetItemId,
                        'type'             => $validated['type'],
                        'direction'        => Transaction::DIRECTION_OUT,
                        'transfer_group_id'=> $transferGroupId,
                        'description'      => $validated['description'] . ' (ke ' . $destSaving->name . ')',
                        'amount'           => $amount,
                        'fee'              => $fee > 0 ? $fee : null,
                        'transaction_date' => $date,
                        'is_manual'        => true,
                    ]);

                    $sourceSaving->decrement('current_balance', $amount + $fee);

                    // 2. Catat transaksi masuk di rekening tujuan
                    Transaction::create([
                        'user_id'          => $userId,
                        'savings_id'       => $destSaving->id,
                        'category_id'      => $categoryId,
                        'type'             => $validated['type'],
                        'direction'        => Transaction::DIRECTION_IN,
                        'transfer_group_id'=> $transferGroupId,
                        'description'      => $validated['description'] . ' (dari ' . $sourceSaving->name . ')',
                        'amount'           => $amount,
                        'fee'              => null,
                        'transaction_date' => $date,
                        'is_manual'        => true,
                    ]);

                    $destSaving->increment('current_balance', $amount);

                    return $txOut;
                }

                // Skenario 2: Transaksi reguler (Cash In atau Cash Out)
                $saving = Saving::where('user_id', $userId)->findOrFail($validated['savings_id']);

                // Validasi saldo minus untuk pengeluaran
                if ($validated['type'] !== 'Cash In' && (float) $saving->current_balance < ($amount + $fee)) {
                    throw new \Exception(
                        "Saldo rekening {$saving->name} tidak mencukupi (Sisa: Rp " . number_format($saving->current_balance, 0, ',', '.') . ", Dibutuhkan: Rp " . number_format($amount + $fee, 0, ',', '.') . ")."
                    );
                }

                $tx = Transaction::create([
                    'user_id'          => $userId,
                    'savings_id'       => $saving->id,
                    'category_id'      => $categoryId,
                    'budget_item_id'   => $budgetItemId,
                    'type'             => $validated['type'],
                    'description'      => $validated['description'],
                    'amount'           => $amount,
                    'fee'              => $fee > 0 ? $fee : null,
                    'transaction_date' => $date,
                    'is_manual'        => true,
                ]);

                if ($validated['type'] === 'Cash In') {
                    $saving->increment('current_balance', $amount);
                } else {
                    $saving->decrement('current_balance', $amount + $fee);
                }

                return $tx;
            });

            return $this->successResponse([
                'id'               => $transaction->id,
                'description'      => $transaction->description,
                'type'             => $transaction->type,
                'amount'           => (float) $transaction->amount,
                'amount_formatted' => 'Rp ' . number_format($transaction->amount, 0, ',', '.'),
            ], 'Transaksi berhasil ditambahkan!', 201);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Menampilkan detail satu transaksi.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->id;

        $tx = Transaction::with(['category', 'savingAccount'])
            ->where('user_id', $userId)
            ->find($id);

        if (!$tx) {
            return $this->errorResponse('Transaksi tidak ditemukan.', 404);
        }

        return $this->successResponse([
            'id'                      => $tx->id,
            'type'                    => $tx->type,
            'amount'                  => (float) $tx->amount,
            'amount_formatted'        => 'Rp ' . number_format($tx->amount, 0, ',', '.'),
            'fee'                     => $tx->fee ? (float) $tx->fee : null,
            'fee_formatted'           => $tx->fee ? 'Rp ' . number_format($tx->fee, 0, ',', '.') : null,
            'description'             => $tx->description,
            'transaction_date'        => $tx->transaction_date ? Carbon::parse($tx->transaction_date)->toIso8601String() : null,
            'transaction_date_formatted' => $tx->transaction_date ? Carbon::parse($tx->transaction_date)->translatedFormat('d F Y, H:i') : null,
            'category'                => $tx->category ? [
                'id'    => $tx->category->id,
                'name'  => $tx->category->name,
                'color' => $tx->category->color,
            ] : null,
            'saving_account'          => $tx->savingAccount ? [
                'id'   => $tx->savingAccount->id,
                'name' => $tx->savingAccount->name,
            ] : null,
        ], 'Detail transaksi berhasil diambil.');
    }

    /**
     * Menghapus transaksi dan otomatis me-rollback saldo rekening.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->id;

        $tx = Transaction::where('user_id', $userId)->find($id);

        if (!$tx) {
            return $this->errorResponse('Transaksi tidak ditemukan.', 404);
        }

        try {
            DB::transaction(function () use ($tx, $userId) {
                // Transfer antar rekening: hapus kedua sisi sekaligus agar saldo tetap konsisten
                $legs = $tx->transfer_group_id
                    ? Transaction::where('user_id', $userId)->where('transfer_group_id', $tx->transfer_group_id)->get()
                    : collect([$tx]);

                foreach ($legs as $leg) {
                    $saving = Saving::find($leg->savings_id);
                    if ($saving) {
                        // Kebalikan dari efek transaksi terhadap saldo (termasuk fee)
                        $saving->decrement('current_balance', $leg->balanceEffect());
                    }

                    $leg->delete();
                }
            });

            return $this->successResponse(null, 'Transaksi berhasil dihapus dan saldo telah disesuaikan kembali.');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menghapus transaksi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mengambil daftar kategori transaksi untuk mobile apps.
     */
    public function categories(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        $query = Category::query();
        if ($userId) {
            $query->forUser($userId);
        }
        $categories = $query->orderBy('name', 'ASC')->get(['id', 'name', 'color', 'icon', 'type']);

        return $this->successResponse($categories, 'Daftar kategori berhasil diambil.');
    }

    /**
     * Preview ekstraksi transaksi dari PDF e-statement bank untuk mobile apps.
     */
    public function importPdfPreview(Request $request, PdfImportService $importService): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file'       => 'required|file|mimes:pdf|max:20480',
            'savings_id' => 'required|exists:savings,id',
            'password'   => 'nullable|string',
        ], [
            'file.required'       => 'Silakan pilih file PDF terlebih dahulu.',
            'file.mimes'          => 'File harus berformat PDF.',
            'file.max'            => 'Ukuran file PDF maksimal 20MB.',
            'savings_id.required' => 'Pilih rekening tujuan.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        $userId = $request->user()->id;
        $saving = Saving::with('bank')->where('id', $request->savings_id)
            ->where('user_id', $userId)
            ->first();

        if (!$saving) {
            return $this->errorResponse('Rekening bank tidak ditemukan atau bukan milik Anda.', 403);
        }

        $bankName = strtolower($saving->bank->name ?? '');
        $bankCode = 'auto';
        if (str_contains($bankName, 'jago')) {
            $bankCode = 'jago';
        } elseif (str_contains($bankName, 'bca')) {
            $bankCode = 'bca';
        } elseif (str_contains($bankName, 'mandiri')) {
            $bankCode = 'mandiri';
        }

        $file = $request->file('file');
        $tempFileName = 'import_api_' . $userId . '_' . time() . '_' . uniqid() . '.pdf';
        $tempPath = $file->storeAs('temp', $tempFileName, 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        try {
            $extractedData = $importService->extract($fullPath, $bankCode, $request->password, $userId, (int) $saving->id);

            return $this->successResponse($extractedData, 'Berhasil mengekstrak transaksi dari PDF.');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            \Log::error('API PDF Import preview error: ' . $e->getMessage());
            return $this->errorResponse('Gagal memproses file PDF: ' . $e->getMessage(), 500);
        } finally {
            if (\Storage::disk('public')->exists($tempPath)) {
                \Storage::disk('public')->delete($tempPath);
            }
        }
    }

    /**
     * Simpan hasil ekstraksi PDF yang telah dikonfirmasi/dimodifikasi user.
     */
    public function importPdfSave(Request $request, PdfImportService $importService): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'savings_id'                     => 'required|exists:savings,id',
            'transactions'                   => 'required|array|min:1',
            'transactions.*.amount'          => 'required|numeric|min:0.01',
            'transactions.*.type'            => 'required|in:Cash In,Cash Out,Transfer,Tabungan',
            'transactions.*.description'     => 'required|string|max:255',
            'transactions.*.transaction_date'=> 'required|date',
            'transactions.*.category_id'     => 'nullable|exists:categories,id',
            'transactions.*.budget_item_id'  => 'nullable|exists:budget_items,id',
            'transactions.*.is_selected'     => 'nullable|boolean',
            'update_balance'                 => 'nullable|boolean',
            'final_balance'                  => 'nullable|numeric',
        ], [
            'transactions.required' => 'Tidak ada transaksi yang dikirim untuk disimpan.',
            'transactions.min'      => 'Minimal pilih satu transaksi untuk disimpan.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $result = $importService->saveImported(
                $request->user(),
                (int) $request->savings_id,
                $request->transactions,
                (bool) $request->input('update_balance', false),
                $request->filled('final_balance') ? (float) $request->final_balance : null,
                (bool) $request->input('skip_duplicates', false)
            );

            return $this->successResponse([
                'count'              => $result['count'],
                'duplicates_skipped' => $result['duplicates_skipped'] ?? 0,
                'total_amount'       => $result['total_amount'],
                'saving_name'        => $result['saving_name'],
            ], "Berhasil menyimpan {$result['count']} transaksi ke rekening {$result['saving_name']}!");
        } catch (\Exception $e) {
            \Log::error('API PDF Import save error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menyimpan transaksi: ' . $e->getMessage(), 500);
        }
    }
}

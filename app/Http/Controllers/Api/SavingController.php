<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan semua rekening / dompet milik pengguna.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $savings = Saving::with('bank')
            ->where('user_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->map(function ($s) {
                return [
                    'id'               => $s->id,
                    'name'             => $s->name,
                    'account_number'   => $s->account_number,
                    'balance'          => (float) $s->current_balance,
                    'balance_formatted'=> 'Rp ' . number_format($s->current_balance, 0, ',', '.'),
                    'is_cash'          => $s->account_number === 'CASH',
                    'bank'             => $s->bank ? [
                        'id'   => $s->bank->id,
                        'code' => $s->bank->code,
                        'name' => $s->bank->name,
                    ] : null,
                    'created_at'       => $s->created_at?->toIso8601String(),
                ];
            });

        $totalBalance = $savings->sum('balance');

        return $this->successResponse([
            'total_balance'           => (float) $totalBalance,
            'total_balance_formatted' => 'Rp ' . number_format($totalBalance, 0, ',', '.'),
            'savings'                 => $savings,
        ], 'Daftar rekening tabungan berhasil diambil.');
    }

    /**
     * Menambahkan rekening tabungan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'bank_id'         => ['nullable', 'exists:banks,id'],
            'name'            => ['required', 'string', 'max:255'],
            'account_number'  => ['required', 'string', 'max:100'],
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $initialBalance = (float) ($validated['initial_balance'] ?? 0);

        try {
            $saving = DB::transaction(function () use ($userId, $validated, $initialBalance) {
                $newSaving = Saving::create([
                    'user_id'         => $userId,
                    'bank_id'         => $validated['bank_id'] ?? null,
                    'name'            => $validated['name'],
                    'account_number'  => $validated['account_number'],
                    'current_balance' => $initialBalance,
                ]);

                // Jika ada saldo awal > 0, catat sebagai transaksi Cash In
                if ($initialBalance > 0) {
                    Transaction::create([
                        'user_id'          => $userId,
                        'savings_id'       => $newSaving->id,
                        'type'             => 'Cash In',
                        'description'      => 'Saldo Awal Rekening ' . $newSaving->name,
                        'amount'           => $initialBalance,
                        'transaction_date' => now(),
                        'is_manual'        => true,
                    ]);
                }

                return $newSaving;
            });

            return $this->successResponse([
                'id'               => $saving->id,
                'name'             => $saving->name,
                'account_number'   => $saving->account_number,
                'balance'          => (float) $saving->current_balance,
                'balance_formatted'=> 'Rp ' . number_format($saving->current_balance, 0, ',', '.'),
            ], 'Rekening tabungan berhasil ditambahkan!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menambahkan rekening tabungan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan rincian satu rekening beserta riwayat transaksinya.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->id;

        $saving = Saving::with('bank')
            ->where('user_id', $userId)
            ->find($id);

        if (!$saving) {
            return $this->errorResponse('Rekening tabungan tidak ditemukan.', 404);
        }

        $transactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->where('savings_id', $saving->id)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(20)
            ->get()
            ->map(function ($tx) {
                return [
                    'id'               => $tx->id,
                    'type'             => $tx->type,
                    'amount'           => (float) $tx->amount,
                    'amount_formatted' => 'Rp ' . number_format($tx->amount, 0, ',', '.'),
                    'description'      => $tx->description,
                    'transaction_date' => $tx->transaction_date ? $tx->transaction_date->format('Y-m-d H:i') : null,
                    'category'         => $tx->category?->name ?? 'Lainnya',
                ];
            });

        return $this->successResponse([
            'saving' => [
                'id'               => $saving->id,
                'name'             => $saving->name,
                'account_number'   => $saving->account_number,
                'balance'          => (float) $saving->current_balance,
                'balance_formatted'=> 'Rp ' . number_format($saving->current_balance, 0, ',', '.'),
                'is_cash'          => $saving->account_number === 'CASH',
                'bank'             => $saving->bank ? [
                    'id'   => $saving->bank->id,
                    'name' => $saving->bank->name,
                ] : null,
            ],
            'recent_transactions' => $transactions,
        ], 'Detail rekening berhasil diambil.');
    }

    /**
     * Memperbarui informasi nama dan nomor rekening.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->id;

        $saving = Saving::where('user_id', $userId)->find($id);

        if (!$saving) {
            return $this->errorResponse('Rekening tabungan tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'bank_id'        => ['nullable', 'exists:banks,id'],
            'name'           => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
        ]);

        // Cegah pengubahan account_number CASH
        if ($saving->account_number === 'CASH' && $validated['account_number'] !== 'CASH') {
            return $this->errorResponse('Nomor akun Dompet Tunai (CASH) tidak dapat diubah.', 422);
        }

        $saving->update($validated);

        return $this->successResponse([
            'id'             => $saving->id,
            'name'           => $saving->name,
            'account_number' => $saving->account_number,
        ], 'Informasi rekening berhasil diperbarui.');
    }

    /**
     * Menghapus rekening tabungan.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->id;

        $saving = Saving::where('user_id', $userId)->find($id);

        if (!$saving) {
            return $this->errorResponse('Rekening tabungan tidak ditemukan.', 404);
        }

        if ($saving->account_number === 'CASH') {
            return $this->errorResponse('Dompet Tunai (Cash) adalah rekening default sistem dan tidak dapat dihapus.', 422);
        }

        if ((float) $saving->current_balance > 0) {
            return $this->errorResponse(
                'Rekening ini masih memiliki saldo Rp ' . number_format($saving->current_balance, 0, ',', '.') . '. Silakan pindahkan saldo terlebih dahulu sebelum menghapus rekening.',
                422
            );
        }

        $saving->delete();

        return $this->successResponse(null, 'Rekening tabungan berhasil dihapus.');
    }

    /**
     * Mengambil daftar bank & e-wallet untuk dropdown mobile apps.
     */
    public function banks(): JsonResponse
    {
        $banks = Bank::orderBy('name', 'ASC')->get(['id', 'code', 'name']);

        return $this->successResponse($banks, 'Daftar bank berhasil diambil.');
    }
}

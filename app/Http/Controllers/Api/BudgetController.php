<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan daftar anggaran bulanan pengguna beserta ringkasan pemakaian riil.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2020 || $year > 2099) {
            $year = (int) now()->year;
        }

        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $budgetItems = $budgets->map(function ($b) {
            return $this->formatBudget($b);
        });

        // Hitung total ringkasan
        $totalBudget = (float) $budgets->sum('amount');
        $totalSpent = (float) $budgets->sum(fn($b) => $b->spent);
        $totalRemaining = (float) ($totalBudget - $totalSpent);
        $overallPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        $overallStatus = 'safe';
        if ($totalSpent > $totalBudget && $totalBudget > 0) {
            $overallStatus = 'overbudget';
        } elseif ($overallPercentage >= 80) {
            $overallStatus = 'warning';
        }

        // Kategori pengeluaran yang belum dianggarkan
        $budgetedCategoryIds = $budgets->pluck('category_id')->toArray();
        $availableCategories = Category::forUser($userId)
            ->where('type', 'Cash Out')
            ->whereNotIn('id', $budgetedCategoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'icon', 'color', 'type']);

        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $this->successResponse([
            'period' => [
                'month'      => $month,
                'year'       => $year,
                'month_name' => $indonesianMonths[$month] ?? Carbon::createFromDate($year, $month, 1)->format('F'),
            ],
            'summary' => [
                'total_budget'              => $totalBudget,
                'total_budget_formatted'    => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
                'total_spent'               => $totalSpent,
                'total_spent_formatted'     => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
                'total_remaining'           => $totalRemaining,
                'total_remaining_formatted' => 'Rp ' . number_format(abs($totalRemaining), 0, ',', '.'),
                'is_overbudget'             => $totalSpent > $totalBudget && $totalBudget > 0,
                'overall_percentage'        => $overallPercentage,
                'overall_status'            => $overallStatus,
                'total_categories'          => $budgets->count(),
            ],
            'budgets'              => $budgetItems,
            'available_categories' => $availableCategories,
        ], 'Data anggaran bulanan berhasil diambil.');
    }

    /**
     * Menyimpan atau memperbarui anggaran kebutuhan bulanan.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'category_id'   => ['nullable'],
            'category_name' => ['nullable', 'string', 'max:50'],
            'kebutuhan'     => ['nullable', 'string', 'max:50'],
            'amount'        => ['required', 'numeric', 'min:1000'],
            'month'         => ['required', 'integer', 'between:1,12'],
            'year'          => ['required', 'integer', 'between:2020,2099'],
        ], [
            'amount.required' => 'Nominal anggaran wajib diisi.',
            'amount.min'      => 'Nominal anggaran minimal Rp 1.000.',
            'month.between'   => 'Bulan harus antara 1 sampai 12.',
            'year.between'    => 'Tahun tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $validated = $validator->validated();
        $kebutuhanName = trim($validated['kebutuhan'] ?? $validated['category_name'] ?? '');

        if (!empty($kebutuhanName)) {
            $cat = Category::findOrCreateKebutuhan($kebutuhanName, $userId);
            $categoryId = $cat->id;
        } elseif (!empty($validated['category_id']) && is_numeric($validated['category_id'])) {
            $categoryId = (int) $validated['category_id'];
        } else {
            return $this->errorResponse('Validasi gagal', 422, ['category_id' => ['Kategori atau nama kebutuhan wajib diisi.']]);
        }

        $budget = Budget::updateOrCreate(
            [
                'user_id'     => $userId,
                'category_id' => $categoryId,
                'month'       => $validated['month'],
                'year'        => $validated['year'],
            ],
            [
                'amount' => $validated['amount'],
            ]
        );

        $budget->load('category');

        return $this->successResponse(
            $this->formatBudget($budget),
            'Anggaran kebutuhan berhasil disimpan.',
            201
        );
    }

    /**
     * Menyimpan beberapa anggaran kebutuhan sekaligus dalam satu request (Batch/Multiple).
     */
    public function batchStore(Request $request, \App\Services\BudgetService $budgetService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items'              => ['required', 'array', 'min:1'],
            'items.*.amount'     => ['required', 'numeric', 'min:1000'],
            'items.*.month'      => ['nullable', 'integer', 'between:1,12'],
            'items.*.year'       => ['nullable', 'integer', 'between:2020,2099'],
        ], [
            'items.required'     => 'Daftar item anggaran wajib diisi.',
            'items.min'          => 'Minimal sertakan 1 item anggaran.',
            'items.*.amount.min' => 'Nominal anggaran minimal Rp 1.000.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        try {
            $result = $budgetService->recordMultipleBudgets($request->user(), $request->input('items'));
            return $this->successResponse([
                'count'                  => $result['count'],
                'total_amount'           => $result['total_amount'],
                'total_amount_formatted' => 'Rp ' . number_format($result['total_amount'], 0, ',', '.'),
                'budgets'                => collect($result['budgets'])->map(fn($b) => $this->formatBudget($b)),
            ], 'Berhasil menyimpan ' . $result['count'] . ' anggaran kebutuhan.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Menampilkan detail satu anggaran kategori.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $budget = Budget::with('category')
            ->where('user_id', $userId)
            ->find($id);

        if (!$budget) {
            return $this->errorResponse('Anggaran tidak ditemukan.', 404);
        }

        return $this->successResponse(
            $this->formatBudget($budget),
            'Detail anggaran berhasil diambil.'
        );
    }

    /**
     * Memperbarui batas nominal anggaran yang sudah ada.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $budget = Budget::where('user_id', $userId)->find($id);

        if (!$budget) {
            return $this->errorResponse('Anggaran tidak ditemukan.', 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:1000'],
        ], [
            'amount.required' => 'Nominal anggaran wajib diisi.',
            'amount.min'      => 'Nominal anggaran minimal Rp 1.000.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $budget->update([
            'amount' => $request->input('amount'),
        ]);

        $budget->load('category');

        return $this->successResponse(
            $this->formatBudget($budget),
            'Nominal anggaran berhasil diperbarui.'
        );
    }

    /**
     * Menghapus anggaran kategori.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $budget = Budget::where('user_id', $userId)->find($id);

        if (!$budget) {
            return $this->errorResponse('Anggaran tidak ditemukan.', 404);
        }

        $budget->delete();

        return $this->successResponse(null, 'Anggaran berhasil dihapus.');
    }

    /**
     * Format model Budget menjadi array response yang seragam.
     */
    private function formatBudget(Budget $b): array
    {
        $spent = (float) $b->spent;
        $amount = (float) $b->amount;
        $remaining = (float) $b->remaining;
        $percentage = (float) $b->percentage;
        $status = $b->status;

        return [
            'id'                  => $b->id,
            'category_id'         => $b->category_id,
            'month'               => $b->month,
            'year'                => $b->year,
            'amount'              => $amount,
            'amount_formatted'    => 'Rp ' . number_format($amount, 0, ',', '.'),
            'spent'               => $spent,
            'spent_formatted'     => 'Rp ' . number_format($spent, 0, ',', '.'),
            'remaining'           => $remaining,
            'remaining_formatted' => 'Rp ' . number_format(abs($remaining), 0, ',', '.'),
            'is_overbudget'       => $spent > $amount,
            'percentage'          => $percentage,
            'status'              => $status, // 'safe', 'warning', 'overbudget'
            'category'            => $b->category ? [
                'id'    => $b->category->id,
                'name'  => $b->category->name,
                'icon'  => $b->category->icon,
                'color' => $b->category->color,
                'type'  => $b->category->type,
            ] : null,
            'created_at'          => $b->created_at?->toIso8601String(),
            'updated_at'          => $b->updated_at?->toIso8601String(),
        ];
    }
}

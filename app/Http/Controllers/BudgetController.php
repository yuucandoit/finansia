<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Menampilkan halaman anggaran bulanan.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        // Validasi input bulan & tahun
        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2020 || $year > 2099) {
            $year = (int) now()->year;
        }

        // Ambil container anggaran bulanan beserta pos-pos kebutuhannya
        $budgetContainer = Budget::with(['items.category', 'items.transactions' => function ($q) {
            $q->orderBy('transaction_date', 'desc');
        }])
        ->where('user_id', $userId)
        ->where('month', $month)
        ->where('year', $year)
        ->first();

        $budgets = $budgetContainer ? $budgetContainer->items : collect([]);

        // Hitung total ringkasan
        $totalBudget = (float) $budgets->sum('amount');
        $totalSpent = (float) $budgets->sum(fn($b) => $b->spent);
        $totalRemaining = $totalBudget - $totalSpent;
        $overallPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        // Kategori pengeluaran (semua kategori Cash Out dapat dipilih)
        $availableCategories = Category::forUser($userId)
            ->where('type', 'Cash Out')
            ->orderBy('name')
            ->get();

        $allCategories = $availableCategories;

        // Helper label bulan
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Cek ketersediaan anggaran pada bulan sebelumnya untuk fitur "Salin dari Bulan Lalu"
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear  = $month === 1 ? $year - 1 : $year;
        $hasPreviousBudgets = Budget::where('user_id', $userId)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->whereHas('items')
            ->exists();

        return view('budget', [
            'budgets'             => $budgets,
            'budgetContainer'     => $budgetContainer,
            'month'               => $month,
            'year'                => $year,
            'prevMonth'           => $prevMonth,
            'prevYear'            => $prevYear,
            'hasPreviousBudgets'  => $hasPreviousBudgets,
            'selectedDate'        => $selectedDate,
            'totalBudget'         => $totalBudget,
            'totalSpent'          => $totalSpent,
            'totalRemaining'      => $totalRemaining,
            'overallPercentage'   => $overallPercentage,
            'availableCategories' => $availableCategories,
            'allCategories'       => $allCategories,
        ]);
    }

    /**
     * Menyimpan anggaran baru atau memperbarui jika sudah ada nama yang sama di bulan/tahun yang sama.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'name'          => ['nullable', 'string', 'max:100'],
            'new_kebutuhan' => ['nullable', 'string', 'max:100'],
            'category_id'   => ['nullable'],
            'amount'        => ['required', 'numeric', 'min:1000'],
            'month'         => ['required', 'integer', 'between:1,12'],
            'year'          => ['required', 'integer', 'between:2020,2099'],
        ], [
            'amount.required' => 'Nominal anggaran wajib diisi.',
            'amount.min'      => 'Nominal anggaran minimal Rp 1.000.',
        ]);

        $name = trim($validated['name'] ?? $validated['new_kebutuhan'] ?? '');
        $categoryId = !empty($validated['category_id']) && is_numeric($validated['category_id'])
            ? (int) $validated['category_id']
            : null;

        if (!$name && !$categoryId) {
            return back()->withErrors(['name' => 'Nama kebutuhan atau kategori wajib diisi.'])->withInput();
        }

        $budgetService = app(\App\Services\BudgetService::class);
        if (!$categoryId) {
            $cat = $budgetService->resolveCategory($name, $userId);
            $categoryId = $cat ? $cat->id : null;
        }

        if (!$categoryId) {
            $cat = Category::where('name', 'Lainnya')->where('type', 'Cash Out')->first()
                ?: Category::where('type', 'Cash Out')->first();
            $categoryId = $cat->id;
        }

        if ($name === '') {
            $cat = Category::find($categoryId);
            $name = $cat ? $cat->name : 'Anggaran';
        }

        Budget::upsertBudget(
            $userId,
            $name,
            $categoryId,
            (int) $validated['month'],
            (int) $validated['year'],
            (float) $validated['amount']
        );

        return redirect()->route('budget.index', [
            'month' => $validated['month'],
            'year'  => $validated['year'],
        ])->with('success', 'Anggaran pos "' . $name . '" berhasil disimpan!');
    }

    /**
     * Memperbarui nominal atau nama batas anggaran pos kebutuhan.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $userId = auth()->id();

        $budgetItem = BudgetItem::with('budget')
            ->whereHas('budget', fn($q) => $q->where('user_id', $userId))
            ->find($id);

        if (!$budgetItem) {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'amount'      => ['required', 'numeric', 'min:1000'],
        ]);

        $updateData = ['amount' => $validated['amount']];
        if (!empty($validated['name'])) {
            $updateData['name'] = trim($validated['name']);
        }
        if (!empty($validated['category_id'])) {
            $updateData['category_id'] = (int) $validated['category_id'];
        }

        $budgetItem->update($updateData);

        return redirect()->route('budget.index', [
            'month' => $budgetItem->budget->month,
            'year'  => $budgetItem->budget->year,
        ])->with('success', 'Anggaran pos "' . $budgetItem->name . '" berhasil diperbarui!');
    }

    /**
     * Menghapus pos batas anggaran.
     */
    public function destroy(int $id): RedirectResponse
    {
        $userId = auth()->id();

        $budgetItem = BudgetItem::with('budget')
            ->whereHas('budget', fn($q) => $q->where('user_id', $userId))
            ->find($id);

        if (!$budgetItem) {
            abort(403);
        }

        $month = $budgetItem->budget->month;
        $year  = $budgetItem->budget->year;
        $name  = $budgetItem->name;

        $budgetItem->delete();

        return redirect()->route('budget.index', [
            'month' => $month,
            'year'  => $year,
        ])->with('success', 'Anggaran pos "' . $name . '" berhasil dihapus.');
    }

    /**
     * Menyalin seluruh anggaran kebutuhan dari bulan sebelumnya ke bulan ini via Web.
     */
    public function copyPrevious(Request $request, \App\Services\BudgetService $budgetService): RedirectResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2020 || $year > 2099) {
            $year = (int) now()->year;
        }

        try {
            $result = $budgetService->copyPreviousMonthBudgets($request->user(), $month, $year);
            return redirect()->route('budget.index', ['month' => $month, 'year' => $year])
                ->with('success', "Berhasil menyalin {$result['count']} anggaran kebutuhan dari bulan sebelumnya!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('budget.index', ['month' => $month, 'year' => $year])
                ->withErrors(['copy' => $e->getMessage()]);
        } catch (\Exception $e) {
            return redirect()->route('budget.index', ['month' => $month, 'year' => $year])
                ->withErrors(['copy' => 'Gagal menyalin anggaran: ' . $e->getMessage()]);
        }
    }

    /**
     * Menampilkan daftar transaksi yang terhubung ke satu pos anggaran (drill-down).
     */
    public function transactions(int $id): JsonResponse
    {
        $userId = auth()->id();

        $budgetItem = BudgetItem::with(['budget', 'category'])
            ->whereHas('budget', fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);

        $startDate = Carbon::createFromDate($budgetItem->budget->year, $budgetItem->budget->month, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($budgetItem->budget->year, $budgetItem->budget->month, 1)->endOfMonth();

        // 1. Direct transactions
        $directTransactions = \App\Models\Transaction::with(['savingAccount', 'category'])
            ->where('budget_item_id', $budgetItem->id)
            ->whereIn('type', ['Cash Out'])
            ->get();

        // 2. Unassigned matching transactions
        $unassignedTransactions = \App\Models\Transaction::with(['savingAccount', 'category'])
            ->where('user_id', $userId)
            ->whereNull('budget_item_id')
            ->where('category_id', $budgetItem->category_id)
            ->whereIn('type', ['Cash Out'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $allTransactions = $directTransactions->merge($unassignedTransactions)->unique('id')->sortByDesc('transaction_date')->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'pos' => [
                    'id'               => $budgetItem->id,
                    'name'             => $budgetItem->name,
                    'amount'           => (float) $budgetItem->amount,
                    'amount_formatted' => 'Rp ' . number_format($budgetItem->amount, 0, ',', '.'),
                    'spent'            => (float) $budgetItem->spent,
                    'spent_formatted'  => 'Rp ' . number_format($budgetItem->spent, 0, ',', '.'),
                    'remaining'        => (float) $budgetItem->remaining,
                    'remaining_formatted' => 'Rp ' . number_format(abs($budgetItem->remaining), 0, ',', '.'),
                    'percentage'       => $budgetItem->percentage,
                    'status'           => $budgetItem->status,
                    'category'         => $budgetItem->category ? [
                        'id'    => $budgetItem->category->id,
                        'name'  => $budgetItem->category->name,
                        'icon'  => $budgetItem->category->icon,
                        'color' => $budgetItem->category->color,
                    ] : null,
                ],
                'transactions' => $allTransactions->map(fn($t) => [
                    'id'                      => $t->id,
                    'description'             => $t->description,
                    'amount'                  => (float) $t->amount,
                    'amount_formatted'        => 'Rp ' . number_format($t->amount, 0, ',', '.'),
                    'transaction_date'        => $t->transaction_date ? Carbon::parse($t->transaction_date)->translatedFormat('d M Y') : null,
                    'account_name'            => $t->savingAccount?->name ?? 'Dompet',
                    'is_direct'               => $t->budget_item_id === $budgetItem->id,
                ]),
            ],
        ]);
    }
}

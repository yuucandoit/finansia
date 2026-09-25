<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
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

        // Ambil seluruh anggaran user pada bulan & tahun ini
        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

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
            ->exists();

        return view('budget', [
            'budgets'             => $budgets,
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
     * Memperbarui nominal atau nama batas anggaran.
     */
    public function update(Request $request, Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== auth()->id()) {
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

        $budget->update($updateData);

        return redirect()->route('budget.index', [
            'month' => $budget->month,
            'year'  => $budget->year,
        ])->with('success', 'Anggaran berhasil diperbarui!');
    }

    /**
     * Menghapus batas anggaran.
     */
    public function destroy(Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== auth()->id()) {
            abort(403);
        }

        $month = $budget->month;
        $year = $budget->year;

        $budget->delete();

        return redirect()->route('budget.index', [
            'month' => $month,
            'year'  => $year,
        ])->with('success', 'Anggaran berhasil dihapus.');
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
}

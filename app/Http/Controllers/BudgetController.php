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

        // Kategori yang belum memiliki anggaran di periode ini
        $budgetedCategoryIds = $budgets->pluck('category_id')->toArray();
        $availableCategories = Category::forUser($userId)
            ->where('type', 'Cash Out')
            ->whereNotIn('id', $budgetedCategoryIds)
            ->orderBy('name')
            ->get();

        $allCategories = Category::forUser($userId)->where('type', 'Cash Out')->orderBy('name')->get();

        // Helper label bulan
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        return view('budget', [
            'budgets'             => $budgets,
            'month'               => $month,
            'year'                => $year,
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
     * Menyimpan anggaran baru atau memperbarui jika sudah ada di bulan/tahun yang sama.
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'category_id'   => ['nullable'],
            'new_kebutuhan' => ['nullable', 'string', 'max:50'],
            'amount'        => ['required', 'numeric', 'min:1000'],
            'month'         => ['required', 'integer', 'between:1,12'],
            'year'          => ['required', 'integer', 'between:2020,2099'],
        ]);

        $newKebutuhan = trim($request->input('new_kebutuhan', ''));
        if (!empty($newKebutuhan)) {
            $cat = Category::findOrCreateKebutuhan($newKebutuhan, $userId);
            $categoryId = $cat->id;
        } elseif (!empty($validated['category_id']) && $validated['category_id'] !== '__custom__') {
            $categoryId = (int) $validated['category_id'];
        } else {
            return back()->withErrors(['category_id' => 'Pilih kategori atau ketik nama kebutuhan baru.'])->withInput();
        }

        Budget::updateOrCreate(
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

        return redirect()->route('budget.index', [
            'month' => $validated['month'],
            'year'  => $validated['year'],
        ])->with('success', 'Anggaran kebutuhan berhasil disimpan!');
    }

    /**
     * Memperbarui nominal batas anggaran.
     */
    public function update(Request $request, Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
        ]);

        $budget->update([
            'amount' => $validated['amount'],
        ]);

        return redirect()->route('budget.index', [
            'month' => $budget->month,
            'year'  => $budget->year,
        ])->with('success', 'Nominal anggaran berhasil diperbarui!');
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
}

<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BudgetService
{
    /**
     * Menyimpan atau memperbarui satu batas anggaran kategori bulanan.
     */
    public function recordBudget(
        User $user,
        int|string $category,
        float $amount,
        ?int $month = null,
        ?int $year = null
    ): Budget {
        if ($amount < 1000) {
            throw new InvalidArgumentException('Nominal anggaran minimal Rp 1.000.');
        }

        $month = $month ?: (int) now()->month;
        $year  = $year ?: (int) now()->year;

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2020 || $year > 2099) {
            $year = (int) now()->year;
        }

        $resolvedCategory = $this->resolveCategory($category, $user->id);
        if (!$resolvedCategory) {
            throw new InvalidArgumentException("Kategori '{$category}' tidak ditemukan.");
        }

        $budget = Budget::updateOrCreate(
            [
                'user_id'     => $user->id,
                'category_id' => $resolvedCategory->id,
                'month'       => $month,
                'year'        => $year,
            ],
            [
                'amount' => $amount,
            ]
        );

        $budget->load('category');

        return $budget;
    }

    /**
     * Menyimpan beberapa anggaran kategori sekaligus (Multiple Budgets) dalam satu transaksi database.
     *
     * @param User $user
     * @param array $items Array of [category_id/category, amount, month (optional), year (optional)]
     * @param int|null $defaultMonth
     * @param int|null $defaultYear
     * @return array
     */
    public function recordMultipleBudgets(
        User $user,
        array $items,
        ?int $defaultMonth = null,
        ?int $defaultYear = null
    ): array {
        if (empty($items)) {
            throw new InvalidArgumentException('Daftar anggaran tidak boleh kosong.');
        }

        $defaultMonth = $defaultMonth ?: (int) now()->month;
        $defaultYear  = $defaultYear ?: (int) now()->year;

        $savedBudgets = [];
        $errors = [];

        DB::transaction(function () use ($user, $items, $defaultMonth, $defaultYear, &$savedBudgets, &$errors) {
            foreach ($items as $index => $item) {
                try {
                    $catIdentifier = $item['category_id'] ?? $item['category'] ?? $item['category_name'] ?? $item['kebutuhan'] ?? null;
                    $amount = (float) ($item['amount'] ?? 0);
                    $m = isset($item['month']) ? (int) $item['month'] : $defaultMonth;
                    $y = isset($item['year']) ? (int) $item['year'] : $defaultYear;

                    if (!$catIdentifier) {
                        $errors[] = "Item #" . ($index + 1) . ": Kategori wajib diisi.";
                        continue;
                    }

                    if ($amount < 1000) {
                        $errors[] = "Item #" . ($index + 1) . ": Nominal minimal Rp 1.000.";
                        continue;
                    }

                    $budget = $this->recordBudget($user, $catIdentifier, $amount, $m, $y);
                    $savedBudgets[] = $budget;
                } catch (\Throwable $e) {
                    $errors[] = "Item #" . ($index + 1) . ": " . $e->getMessage();
                }
            }
        });

        if (!empty($errors) && empty($savedBudgets)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $totalAmount = collect($savedBudgets)->sum('amount');

        return [
            'success'      => true,
            'count'        => count($savedBudgets),
            'total_amount' => (float) $totalAmount,
            'budgets'      => $savedBudgets,
            'errors'       => $errors,
        ];
    }

    /**
     * Mengambil ringkasan anggaran bulanan pengguna.
     */
    public function getBudgetSummary(User $user, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?: (int) now()->month;
        $year  = $year ?: (int) now()->year;

        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $totalBudget = (float) $budgets->sum('amount');
        $totalSpent = (float) $budgets->sum(fn($b) => $b->spent);
        $totalRemaining = (float) ($totalBudget - $totalSpent);
        $overallPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        return [
            'month'              => $month,
            'year'               => $year,
            'total_budget'       => $totalBudget,
            'total_spent'        => $totalSpent,
            'total_remaining'    => $totalRemaining,
            'overall_percentage' => $overallPercentage,
            'count'              => $budgets->count(),
            'budgets'            => $budgets,
        ];
    }

    /**
     * Resolves a category by ID or by name matching, or creates a new kebutuhan category for the user.
     */
    protected function resolveCategory(int|string $identifier, ?int $userId = null): ?Category
    {
        if (is_numeric($identifier)) {
            return Category::find((int) $identifier);
        }

        $identifierLower = strtolower(trim($identifier));

        // Exact match pada kategori sistem atau milik user
        $query = Category::whereRaw('LOWER(name) = ?', [$identifierLower]);
        if ($userId) {
            $query->forUser($userId);
        }
        $cat = $query->first();
        if ($cat) {
            return $cat;
        }

        // Fuzzy search match
        $all = $userId ? Category::forUser($userId)->get() : Category::all();
        foreach ($all as $item) {
            $nameLower = strtolower($item->name);
            if (str_contains($identifierLower, $nameLower) || str_contains($nameLower, $identifierLower)) {
                return $item;
            }
        }

        // If category contains keywords like 'makan', 'minum', 'belanja', 'transport'
        $aliases = [
            'makan'     => 'Makanan & Minuman',
            'minum'     => 'Makanan & Minuman',
            'kuliner'   => 'Makanan & Minuman',
            'food'      => 'Makanan & Minuman',
            'jajan'     => 'Makanan & Minuman',
            'kopi'      => 'Makanan & Minuman',
            'belanja'   => 'Belanja',
            'shopping'  => 'Belanja',
            'bensin'    => 'Transportasi',
            'ongkos'    => 'Transportasi',
            'ojol'      => 'Transportasi',
            'transport' => 'Transportasi',
            'listrik'   => 'Utilitas & Tagihan',
            'air'       => 'Utilitas & Tagihan',
            'pulsa'     => 'Utilitas & Tagihan',
            'paket'     => 'Utilitas & Tagihan',
            'kuota'     => 'Utilitas & Tagihan',
            'wifi'      => 'Utilitas & Tagihan',
            'tagihan'   => 'Utilitas & Tagihan',
            'game'      => 'Hiburan',
            'nonton'    => 'Hiburan',
            'hiburan'   => 'Hiburan',
            'obat'      => 'Kesehatan',
            'dokter'    => 'Kesehatan',
            'kesehatan' => 'Kesehatan',
        ];

        foreach ($aliases as $keyword => $targetName) {
            if (str_contains($identifierLower, $keyword)) {
                $matched = Category::where('name', $targetName)->first();
                if ($matched) {
                    return $matched;
                }
            }
        }

        // Jika kebutuhan baru belum ada di daftar kategori, buatkan otomatis untuk user!
        if ($userId && !empty(trim($identifier))) {
            return Category::findOrCreateKebutuhan($identifier, $userId);
        }

        return Category::where('type', 'Cash Out')->first();
    }
}

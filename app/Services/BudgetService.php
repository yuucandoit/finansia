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
     * Menyimpan atau memperbarui satu batas anggaran bulanan per nama pos kebutuhan.
     */
    public function recordBudget(
        User $user,
        int|string $category,
        float $amount,
        ?int $month = null,
        ?int $year = null,
        ?string $name = null
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
            $resolvedCategory = Category::where('name', 'Lainnya')->where('type', 'Cash Out')->first()
                ?: Category::where('type', 'Cash Out')->first();
        }

        if (!$resolvedCategory) {
            throw new InvalidArgumentException("Kategori '{$category}' tidak ditemukan.");
        }

        // Tentukan nama pos anggaran
        $budgetName = trim($name ?? '');
        if ($budgetName === '') {
            $budgetName = (is_string($category) && !is_numeric($category)) ? trim($category) : $resolvedCategory->name;
        }

        $budget = Budget::upsertBudget(
            $user->id,
            $budgetName,
            $resolvedCategory->id,
            $month,
            $year,
            $amount
        );

        $budget->load('category');

        return $budget;
    }

    /**
     * Menyimpan beberapa anggaran pos kebutuhan sekaligus (Multiple Budgets) dalam satu transaksi database.
     *
     * @param User $user
     * @param array $items Array of [name/kebutuhan, category/category_id, amount, month (optional), year (optional)]
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
                    $name = $item['name'] ?? $item['kebutuhan'] ?? $item['title'] ?? $item['nama'] ?? null;
                    $catIdentifier = $item['category_id'] ?? $item['category'] ?? $item['category_name'] ?? null;
                    $amount = (float) ($item['amount'] ?? 0);
                    $m = isset($item['month']) ? (int) $item['month'] : $defaultMonth;
                    $y = isset($item['year']) ? (int) $item['year'] : $defaultYear;

                    if (!$name && !$catIdentifier) {
                        $errors[] = "Item #" . ($index + 1) . ": Nama kebutuhan atau kategori wajib diisi.";
                        continue;
                    }

                    if ($amount < 1000) {
                        $errors[] = "Item #" . ($index + 1) . ": Nominal minimal Rp 1.000.";
                        continue;
                    }

                    // Jika nama ada tapi kategori tidak spesifik, resolve kategori dari nama
                    $budget = $this->recordBudget($user, $catIdentifier ?: $name, $amount, $m, $y, $name ?: (string) $catIdentifier);
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
            ->orderBy('id', 'desc')
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
     * Menyalin seluruh anggaran kebutuhan dari bulan sebelumnya ke bulan target.
     */
    public function copyPreviousMonthBudgets(User $user, int $targetMonth, int $targetYear): array
    {
        $prevMonth = $targetMonth === 1 ? 12 : $targetMonth - 1;
        $prevYear  = $targetMonth === 1 ? $targetYear - 1 : $targetYear;

        $previousBudgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->get();

        if ($previousBudgets->isEmpty()) {
            throw new InvalidArgumentException("Tidak ada data anggaran pada bulan sebelumnya ({$prevMonth}/{$prevYear}) untuk disalin.");
        }

        $copied = [];
        DB::transaction(function () use ($user, $previousBudgets, $targetMonth, $targetYear, &$copied) {
            foreach ($previousBudgets as $pb) {
                $b = Budget::upsertBudget(
                    $user->id,
                    $pb->name ?: ($pb->category?->name ?? 'Anggaran'),
                    $pb->category_id,
                    $targetMonth,
                    $targetYear,
                    $pb->amount
                );
                $b->load('category');
                $copied[] = $b;
            }
        });

        return [
            'count'      => count($copied),
            'from_month' => $prevMonth,
            'from_year'  => $prevYear,
            'to_month'   => $targetMonth,
            'to_year'    => $targetYear,
            'budgets'    => $copied,
        ];
    }

    /**
     * Resolves a category by ID or by name/keyword matching to standard categories.
     */
    public function resolveCategory(int|string $identifier, ?int $userId = null): ?Category
    {
        if (is_numeric($identifier)) {
            return Category::find((int) $identifier);
        }

        $identifierLower = strtolower(trim((string) $identifier));

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

        // Keyword aliases mapping to master standard categories
        $aliases = [
            'tempat tinggal' => 'Tempat Tinggal',
            'kontrakan'      => 'Tempat Tinggal',
            'kos'            => 'Tempat Tinggal',
            'kost'           => 'Tempat Tinggal',
            'sewa rumah'     => 'Tempat Tinggal',
            'cicilan rumah'  => 'Tempat Tinggal',
            'kpr'            => 'Tempat Tinggal',
            'rumah'          => 'Tempat Tinggal',

            'tabungan'       => 'Tabungan & Investasi',
            'investasi'      => 'Tabungan & Investasi',
            'nabung'         => 'Tabungan & Investasi',
            'deposito'       => 'Tabungan & Investasi',
            'reksadana'      => 'Tabungan & Investasi',
            'saham'          => 'Tabungan & Investasi',
            'crypto'         => 'Tabungan & Investasi',
            'emas'           => 'Tabungan & Investasi',

            'keluarga'       => 'Keluarga & Sedekah',
            'sedekah'        => 'Keluarga & Sedekah',
            'infaq'          => 'Keluarga & Sedekah',
            'zakat'          => 'Keluarga & Sedekah',
            'mamah'          => 'Keluarga & Sedekah',
            'ibu'            => 'Keluarga & Sedekah',
            'ayah'           => 'Keluarga & Sedekah',
            'bapak'          => 'Keluarga & Sedekah',
            'ortu'           => 'Keluarga & Sedekah',
            'orang tua'      => 'Keluarga & Sedekah',

            'pendidikan'     => 'Pendidikan',
            'sekolah'        => 'Pendidikan',
            'kuliah'         => 'Pendidikan',
            'kursus'         => 'Pendidikan',
            'les'            => 'Pendidikan',
            'buku'           => 'Pendidikan',
            'spp'            => 'Pendidikan',
            'ujian'          => 'Pendidikan',

            'makan'          => 'Makanan & Minuman',
            'minum'          => 'Makanan & Minuman',
            'kuliner'        => 'Makanan & Minuman',
            'food'           => 'Makanan & Minuman',
            'jajan'          => 'Makanan & Minuman',
            'kopi'           => 'Makanan & Minuman',
            'cafe'           => 'Makanan & Minuman',
            'restoran'       => 'Makanan & Minuman',

            'belanja'        => 'Belanja',
            'shopping'       => 'Belanja',
            'supermarket'    => 'Belanja',
            'minimarket'     => 'Belanja',
            'alfamart'       => 'Belanja',
            'indomaret'      => 'Belanja',
            'shopee'         => 'Belanja',
            'tokopedia'      => 'Belanja',

            'bensin'         => 'Transportasi',
            'ongkos'         => 'Transportasi',
            'ojol'           => 'Transportasi',
            'grab'           => 'Transportasi',
            'gojek'          => 'Transportasi',
            'transport'      => 'Transportasi',
            'parkir'         => 'Transportasi',
            'tol'            => 'Transportasi',

            'listrik'        => 'Utilitas & Tagihan',
            'air'            => 'Utilitas & Tagihan',
            'pdam'           => 'Utilitas & Tagihan',
            'pulsa'          => 'Utilitas & Tagihan',
            'paket'          => 'Utilitas & Tagihan',
            'kuota'          => 'Utilitas & Tagihan',
            'wifi'           => 'Utilitas & Tagihan',
            'tagihan'        => 'Utilitas & Tagihan',
            'indihome'       => 'Utilitas & Tagihan',
            'iuran'          => 'Utilitas & Tagihan',

            'game'           => 'Hiburan',
            'nonton'         => 'Hiburan',
            'hiburan'        => 'Hiburan',
            'bioskop'        => 'Hiburan',
            'netflix'        => 'Hiburan',
            'spotify'        => 'Hiburan',
            'liburan'        => 'Hiburan',

            'obat'           => 'Kesehatan',
            'dokter'         => 'Kesehatan',
            'kesehatan'      => 'Kesehatan',
            'apotek'         => 'Kesehatan',
            'rumah sakit'    => 'Kesehatan',
            'klinik'         => 'Kesehatan',
            'vitamin'        => 'Kesehatan',
            'bpjs'           => 'Kesehatan',
        ];

        foreach ($aliases as $keyword => $targetName) {
            if (str_contains($identifierLower, $keyword)) {
                $matched = Category::where('name', $targetName)->first();
                if ($matched) {
                    return $matched;
                }
            }
        }

        // Fallback to "Lainnya" (Cash Out) or the first Cash Out category
        $fallback = Category::where('name', 'Lainnya')->where('type', 'Cash Out')->first();
        if ($fallback) {
            return $fallback;
        }

        return Category::where('type', 'Cash Out')->first();
    }
}

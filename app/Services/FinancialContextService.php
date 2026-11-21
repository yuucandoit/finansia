<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\FuturePlan;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialContextService
{
    public function getUserSnapshot(User $user): array
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Rekening & Total Saldo
        $accounts = $user->savings()->get();
        $totalBalance = $accounts->sum('current_balance');

        // 2. Cash Flow Bulan Berjalan
        $monthlyIncome = $user->transactions()
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereIn('type', ['Cash In', 'cash_in'])
            ->sum('amount');

        $monthlyExpense = $user->transactions()
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereIn('type', ['Cash Out', 'cash_out', 'Transfer', 'Tabungan'])
            ->sum('amount');

        // 3. Breakdown Top Pengeluaran per Kategori Bulan Ini
        $categoryBreakdown = DB::table('transactions')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $user->id)
            ->whereBetween('transactions.transaction_date', [$startOfMonth, $endOfMonth])
            ->whereIn('transactions.type', ['Cash Out', 'cash_out', 'Transfer', 'Tabungan'])
            ->select(DB::raw("COALESCE(categories.name, 'Lainnya') as category_name"), DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->pluck('total', 'category_name')
            ->map(fn($val) => (int) $val)
            ->toArray();

        // 4. 10 Transaksi Paling Terakhir
        $recentTransactions = $user->transactions()
            ->with(['category', 'savingAccount'])
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'type'             => $t->type,
                'amount'           => (int) $t->amount,
                'category'         => $t->category->name ?? 'Lainnya',
                'rekening'         => $t->savingAccount->name ?? '-',
                'description'      => $t->description,
                'transaction_date' => $t->transaction_date?->format('Y-m-d') ?? (string) $t->transaction_date,
            ])
            ->toArray();

        // 4b. Top 5 Pengeluaran Terbesar Bulan Ini
        $top5Expenses = $user->transactions()
            ->with(['category', 'savingAccount'])
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereIn('type', ['Cash Out', 'cash_out'])
            ->orderByDesc('amount')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'description'      => $t->description,
                'amount'           => (int) $t->amount,
                'category'         => $t->category->name ?? 'Lainnya',
                'rekening'         => $t->savingAccount->name ?? '-',
                'transaction_date' => $t->transaction_date?->format('Y-m-d') ?? (string) $t->transaction_date,
            ])
            ->toArray();

        // 4c. Top 5 Pemasukan Terbesar Bulan Ini
        $top5Incomes = $user->transactions()
            ->with(['category', 'savingAccount'])
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereIn('type', ['Cash In', 'cash_in'])
            ->orderByDesc('amount')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'description'      => $t->description,
                'amount'           => (int) $t->amount,
                'category'         => $t->category->name ?? 'Lainnya',
                'rekening'         => $t->savingAccount->name ?? '-',
                'transaction_date' => $t->transaction_date?->format('Y-m-d') ?? (string) $t->transaction_date,
            ])
            ->toArray();

        // 4d. Pengeluaran Minggu Ini
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $weeklyExpense = (int) $user->transactions()
            ->whereBetween('transaction_date', [$weekStart, $weekEnd])
            ->whereIn('type', ['Cash Out', 'cash_out'])
            ->sum('amount');

        // 5. Anggaran Bulanan & Status Pemakaian
        $currentMonth = (int) now()->month;
        $currentYear  = (int) now()->year;
        $budget = $user->budgets()
            ->with(['items.category'])
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        $items = $budget ? $budget->items : collect([]);

        $totalBudget = (float) $items->sum('amount');
        $totalBudgetSpent = (float) $items->sum(fn($b) => $b->spent);

        $budgetSnapshot = [
            'total_anggaran'    => (int) $totalBudget,
            'total_terpakai'    => (int) $totalBudgetSpent,
            'sisa_anggaran'     => (int) ($totalBudget - $totalBudgetSpent),
            'status_agregat'    => $totalBudgetSpent > $totalBudget && $totalBudget > 0 ? 'overbudget' : ($totalBudget > 0 && ($totalBudgetSpent / $totalBudget) >= 0.8 ? 'warning' : 'safe'),
            'daftar_anggaran'   => $items->map(fn($b) => [
                'pos'        => $b->name,
                'kategori'   => $b->category->name ?? 'Lainnya',
                'batas'      => (int) $b->amount,
                'terpakai'   => (int) $b->spent,
                'sisa'       => (int) $b->remaining,
                'persentase' => (float) $b->percentage,
                'status'     => $b->status,
            ])->toArray(),
        ];

        return [
            'periode_bulan'            => Carbon::now()->translatedFormat('F Y'),
            'total_saldo'              => (int) $totalBalance,
            'daftar_rekening'          => $accounts->map(fn($a) => [
                'nama'  => $a->name,
                'saldo' => (int) $a->current_balance
            ])->toArray(),
            'cashflow_bulan_ini'       => [
                'total_pemasukan'        => (int) $monthlyIncome,
                'total_pengeluaran'      => (int) $monthlyExpense,
                'pengeluaran_minggu_ini' => $weeklyExpense,
                'sisa_kas'               => (int) ($monthlyIncome - $monthlyExpense),
            ],
            'kesehatan_finansial'         => $this->computeHealthMetrics($user, (float) $totalBalance, (float) $monthlyIncome, (float) $monthlyExpense),
            'pengeluaran_per_kategori'    => $categoryBreakdown,
            'top_5_pengeluaran_bulan_ini' => $top5Expenses,
            'top_5_pemasukan_bulan_ini'   => $top5Incomes,
            'transaksi_terbaru'           => $recentTransactions,
            'anggaran_bulan_ini'       => $budgetSnapshot,
            'rencana_masa_depan'       => $this->getFuturePlansSnapshot($user),
            'hutang_piutang'           => $this->getDebtsSnapshot($user),
            'transaksi_rutin_aktif'    => $this->getRecurringSnapshot($user),
        ];
    }

    protected function computeHealthMetrics(User $user, float $totalBalance, float $monthlyIncome, float $monthlyExpense): array
    {
        $prevStart = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $prevEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $prevMonthExpense = (float) $user->transactions()
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->whereIn('type', ['Cash Out', 'cash_out', 'Transfer', 'Tabungan'])
            ->sum('amount');

        $savingsRate = $monthlyIncome > 0
            ? round((($monthlyIncome - $monthlyExpense) / $monthlyIncome) * 100, 1)
            : 0.0;

        $monthlyBaseline = $monthlyExpense > 0 ? $monthlyExpense : ($prevMonthExpense > 0 ? $prevMonthExpense : 0);
        $emergencyRunwayMonths = $monthlyBaseline > 0
            ? round($totalBalance / $monthlyBaseline, 1)
            : ($totalBalance > 0 ? 12.0 : 0.0);

        return [
            'savings_rate_persen'       => $savingsRate,
            'rasio_dana_darurat_bulan'  => $emergencyRunwayMonths,
            'pengeluaran_bulan_lalu'    => (int) $prevMonthExpense,
            'selisih_pengeluaran_bulan' => (int) ($monthlyExpense - $prevMonthExpense),
        ];
    }

    protected function getFuturePlansSnapshot(User $user): array
    {
        $plans = FuturePlan::with(['rootItems.allChildren', 'items', 'savingAccount'])
            ->accessibleBy($user->id)
            ->whereIn('status', [FuturePlan::STATUS_PLANNED, FuturePlan::STATUS_IN_PROGRESS])
            ->latest('id')
            ->limit(5)
            ->get();

        return $plans->map(function (FuturePlan $plan) {
            return [
                'judul'                 => $plan->title,
                'target_tanggal'        => $plan->target_date?->format('Y-m-d'),
                'total_estimasi'        => (int) $plan->total_estimated_amount,
                'sudah_terbeli'         => (int) $plan->total_completed_amount,
                'sisa_kebutuhan'        => (int) $plan->remaining_amount,
                'progres_persen'        => (float) $plan->progress_percentage,
                'saldo_rekening_terkait'=> $plan->savingAccount ? (int) $plan->savingAccount->current_balance : null,
            ];
        })->toArray();
    }

    protected function getDebtsSnapshot(User $user): array
    {
        $debts = Debt::where('user_id', $user->id)
            ->where('status', '!=', Debt::STATUS_PAID)
            ->orderBy('due_date')
            ->get();

        return [
            'total_sisa_hutang'  => (int) $debts->where('type', Debt::TYPE_PAYABLE)->sum('remaining_amount'),
            'total_sisa_piutang' => (int) $debts->where('type', Debt::TYPE_RECEIVABLE)->sum('remaining_amount'),
            'jumlah_jatuh_tempo' => $debts->filter(fn (Debt $d) => $d->is_overdue)->count(),
            'daftar_aktif'       => $debts->take(5)->map(fn (Debt $d) => [
                'jenis'      => $d->type === Debt::TYPE_PAYABLE ? 'Hutang' : 'Piutang',
                'pihak'      => $d->person_name,
                'sisa'       => (int) $d->remaining_amount,
                'jatuh_tempo'=> $d->due_date?->format('Y-m-d'),
            ])->toArray(),
        ];
    }

    protected function getRecurringSnapshot(User $user): array
    {
        $recurrings = RecurringTransaction::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        return [
            'jumlah_jadwal'       => $recurrings->count(),
            'pemasukan_rutin'     => (int) $recurrings->where('type', 'Cash In')->sum('amount'),
            'pengeluaran_rutin'   => (int) $recurrings->where('type', 'Cash Out')->sum('amount'),
        ];
    }

    /**
     * Menjalankan query dinamis read-only ke database transaksi milik user berdasarkan parameter AI.
     */
    public function runDynamicQuery(User $user, array $params): array
    {
        $query = $user->transactions()->with(['category', 'savingAccount']);

        if (!empty($params['type']) && in_array($params['type'], ['Cash In', 'Cash Out', 'Transfer', 'Tabungan'], true)) {
            $query->where('type', $params['type']);
        }

        if (!empty($params['date_from'])) {
            $query->whereDate('transaction_date', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->whereDate('transaction_date', '<=', $params['date_to']);
        }

        if (!empty($params['category'])) {
            $catSearch = strtolower(trim($params['category']));
            $query->whereHas('category', function ($q) use ($catSearch) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $catSearch . '%']);
            });
        }

        if (!empty($params['account'])) {
            $accSearch = strtolower(trim($params['account']));
            $query->whereHas('savingAccount', function ($q) use ($accSearch) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $accSearch . '%']);
            });
        }

        if (!empty($params['search'])) {
            $descSearch = strtolower(trim($params['search']));
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . $descSearch . '%']);
        }

        if (isset($params['min_amount']) && is_numeric($params['min_amount'])) {
            $query->where('amount', '>=', (float) $params['min_amount']);
        }

        if (isset($params['max_amount']) && is_numeric($params['max_amount'])) {
            $query->where('amount', '<=', (float) $params['max_amount']);
        }

        $totalMatched = (clone $query)->count();
        $totalAmount = (int) (clone $query)->sum('amount');

        $sortBy = $params['sort_by'] ?? 'amount_desc';
        match ($sortBy) {
            'amount_asc' => $query->orderBy('amount', 'asc'),
            'date_desc'  => $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc'),
            'date_asc'   => $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc'),
            default      => $query->orderBy('amount', 'desc')->orderBy('transaction_date', 'desc'),
        };

        $limit = max(1, min(20, (int) ($params['limit'] ?? 5)));

        $rows = $query->limit($limit)->get()->map(fn($t) => [
            'description'      => $t->description,
            'type'             => $t->type,
            'amount'           => (int) $t->amount,
            'category'         => $t->category->name ?? 'Lainnya',
            'rekening'         => $t->savingAccount->name ?? '-',
            'transaction_date' => $t->transaction_date?->format('Y-m-d') ?? (string) $t->transaction_date,
        ])->toArray();

        return [
            'filter_diterapkan' => array_filter([
                'type'       => $params['type'] ?? null,
                'date_from'  => $params['date_from'] ?? null,
                'date_to'    => $params['date_to'] ?? null,
                'category'   => $params['category'] ?? null,
                'account'    => $params['account'] ?? null,
                'search'     => $params['search'] ?? null,
                'min_amount' => $params['min_amount'] ?? null,
                'limit'      => $limit,
            ]),
            'total_transaksi_cocok' => $totalMatched,
            'total_nominal'         => $totalAmount,
            'hasil_daftar'          => $rows,
        ];
    }
}

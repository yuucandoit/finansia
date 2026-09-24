<?php

namespace App\Services;

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

        // 4. 5 Transaksi Paling Terakhir
        $recentTransactions = $user->transactions()
            ->with('category')
            ->orderBy('transaction_date', 'DESC')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'type' => $t->type,
                'amount' => (int) $t->amount,
                'category' => $t->category->name ?? 'Lainnya',
                'description' => $t->description,
                'transaction_date' => $t->transaction_date,
            ])
            ->toArray();

        // 5. Anggaran Bulanan & Status Pemakaian
        $currentMonth = (int) now()->month;
        $currentYear  = (int) now()->year;
        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $totalBudget = (float) $budgets->sum('amount');
        $totalBudgetSpent = (float) $budgets->sum(fn($b) => $b->spent);

        $budgetSnapshot = [
            'total_anggaran'    => (int) $totalBudget,
            'total_terpakai'    => (int) $totalBudgetSpent,
            'sisa_anggaran'     => (int) ($totalBudget - $totalBudgetSpent),
            'status_agregat'    => $totalBudgetSpent > $totalBudget && $totalBudget > 0 ? 'overbudget' : ($totalBudget > 0 && ($totalBudgetSpent / $totalBudget) >= 0.8 ? 'warning' : 'safe'),
            'daftar_anggaran'   => $budgets->map(fn($b) => [
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
                'total_pemasukan'   => (int) $monthlyIncome,
                'total_pengeluaran' => (int) $monthlyExpense,
                'sisa_kas'          => (int) ($monthlyIncome - $monthlyExpense),
            ],
            'pengeluaran_per_kategori' => $categoryBreakdown,
            'transaksi_terbaru'        => $recentTransactions,
            'anggaran_bulan_ini'       => $budgetSnapshot,
        ];
    }
}

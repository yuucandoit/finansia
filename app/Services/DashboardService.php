<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Sumber data tunggal untuk dashboard web & API.
 *
 * Aturan perhitungan:
 * - Pemasukan  : uang masuk yang bukan transfer antar rekening sendiri.
 * - Pengeluaran: uang keluar yang bukan transfer internal / alokasi tabungan, ditambah SEMUA fee.
 * - Transfer internal (punya transfer_group_id) tidak dihitung sebagai pemasukan maupun pengeluaran.
 * - Saldo historis dihitung mundur dari saldo rekening saat ini memakai efek saldo tiap transaksi.
 */
class DashboardService
{
    public const FEE_CATEGORY = 'Biaya Admin & Transfer';
    public const UNCATEGORIZED = 'Belum Dikategorikan';

    public function __construct(protected BudgetService $budgetService)
    {
    }

    public function build(User $user, ?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy();
        $today = $now->copy()->endOfDay();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $prevStart = $startOfMonth->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();
        $windowStart = $startOfMonth->copy()->subMonthsNoOverflow(11)->startOfMonth();

        $savings = Saving::with('bank')->where('user_id', $user->id)->get();
        $totalBalance = (float) $savings->sum('current_balance');

        // Satu query untuk semua transaksi dalam jendela 12 bulan (+ transaksi bertanggal ke depan)
        $transactions = Transaction::where('user_id', $user->id)
            ->where('transaction_date', '>=', $windowStart->toDateString())
            ->get(['id', 'savings_id', 'category_id', 'budget_item_id', 'type', 'direction', 'transfer_group_id', 'description', 'amount', 'fee', 'transaction_date']);

        $categories = Category::whereIn('id', $transactions->pluck('category_id')->filter()->unique())
            ->get(['id', 'name', 'color'])
            ->keyBy('id');

        $inRange = fn (Carbon $from, Carbon $to) => $transactions->filter(
            fn (Transaction $t) => $t->transaction_date->between($from, $to)
        );

        $thisMonthTx = $inRange($startOfMonth, $endOfMonth);
        $prevMonthTx = $inRange($prevStart, $prevEnd);

        $current = $this->summarizeFlows($thisMonthTx);
        $previous = $this->summarizeFlows($prevMonthTx);

        // Kekayaan bersih: saldo awal bulan = saldo sekarang - efek semua transaksi sejak awal bulan
        $effectSince = fn (Carbon $from) => (float) $transactions
            ->filter(fn (Transaction $t) => $t->transaction_date->gte($from))
            ->sum(fn (Transaction $t) => $t->balanceEffect());

        $startOfMonthBalance = $totalBalance - $effectSince($startOfMonth);
        $netWorthChange = $totalBalance - $startOfMonthBalance;

        return [
            'period' => [
                'month' => (int) $now->month,
                'year'  => (int) $now->year,
                'label' => $now->translatedFormat('F Y'),
            ],
            'net_worth' => [
                'current'        => $totalBalance,
                'start_of_month' => $startOfMonthBalance,
                'change'         => $netWorthChange,
                'change_pct'     => $this->pct($netWorthChange, abs($startOfMonthBalance)),
            ],
            'cashflow' => [
                'income'             => $this->compare($current['income'], $previous['income']),
                'expense'            => $this->compare($current['expense'], $previous['expense']),
                'savings_allocation' => $this->compare($current['savings'], $previous['savings']),
                'internal_transfer'  => $current['internal'],
                'net'                => $this->compare($current['income'] - $current['expense'], $previous['income'] - $previous['expense']),
                'savings_rate'       => $current['income'] > 0
                    ? round((($current['income'] - $current['expense']) / $current['income']) * 100, 1)
                    : null,
            ],
            'spending_by_category' => $this->spendingByCategory($thisMonthTx, $prevMonthTx, $categories, $current['expense']),
            'uncategorized' => $this->uncategorized($thisMonthTx),
            'top_expenses' => $this->topExpenses($thisMonthTx, $categories, $savings),
            'accounts' => $this->accounts($savings, $thisMonthTx),
            'budget' => $this->budget($user, $now, $thisMonthTx),
            'charts' => [
                'monthly_12'       => $this->monthlySeries($transactions, $totalBalance, $windowStart, $now),
                'daily_balance_30' => $this->dailyBalance($transactions, $totalBalance, $today, 30),
                'cash_flow_7_days' => $this->dailyFlows($transactions, $today, 7),
            ],
        ];
    }

    /**
     * Hitung total per kelompok arus uang.
     */
    public function summarizeFlows(Collection $transactions): array
    {
        $result = ['income' => 0.0, 'expense' => 0.0, 'savings' => 0.0, 'internal' => 0.0];

        foreach ($transactions as $t) {
            $amount = (float) $t->amount;
            $fee = (float) ($t->fee ?? 0);

            switch ($t->flowGroup()) {
                case Transaction::FLOW_INCOME:
                    $result['income'] += $amount;
                    break;
                case Transaction::FLOW_EXPENSE:
                    $result['expense'] += $amount;
                    break;
                case Transaction::FLOW_SAVINGS:
                    $result['savings'] += $amount;
                    break;
                case Transaction::FLOW_INTERNAL:
                    if ($t->direction === Transaction::DIRECTION_OUT) {
                        $result['internal'] += $amount;
                    }
                    break;
            }

            // Fee selalu merupakan uang yang benar-benar hilang
            if ($t->direction === Transaction::DIRECTION_OUT) {
                $result['expense'] += $fee;
            }
        }

        return $result;
    }

    protected function spendingByCategory(Collection $thisMonth, Collection $prevMonth, Collection $categories, float $totalExpense): array
    {
        $current = $this->groupExpenseByCategory($thisMonth, $categories);
        $previous = $this->groupExpenseByCategory($prevMonth, $categories);

        return collect($current)
            ->map(function ($row, $key) use ($previous, $totalExpense) {
                $prev = $previous[$key]['amount'] ?? 0.0;

                return [
                    'category_id'     => $row['category_id'],
                    'name'            => $row['name'],
                    'color'           => $row['color'],
                    'amount'          => $row['amount'],
                    'count'           => $row['count'],
                    'pct'             => $this->pct($row['amount'], $totalExpense) ?? 0,
                    'prev_amount'     => $prev,
                    'change_pct'      => $this->pct($row['amount'] - $prev, $prev),
                    'is_uncategorized'=> $row['category_id'] === null && $row['name'] === self::UNCATEGORIZED,
                ];
            })
            ->sortByDesc('amount')
            ->values()
            ->all();
    }

    protected function groupExpenseByCategory(Collection $transactions, Collection $categories): array
    {
        $groups = [];

        $add = function (string $key, ?int $categoryId, string $name, ?string $color, float $amount) use (&$groups) {
            if ($amount <= 0) {
                return;
            }
            $groups[$key] ??= ['category_id' => $categoryId, 'name' => $name, 'color' => $color, 'amount' => 0.0, 'count' => 0];
            $groups[$key]['amount'] += $amount;
            $groups[$key]['count']++;
        };

        foreach ($transactions as $t) {
            if ($t->direction !== Transaction::DIRECTION_OUT) {
                continue;
            }

            $fee = (float) ($t->fee ?? 0);

            if ($t->flowGroup() === Transaction::FLOW_EXPENSE) {
                $category = $t->category_id ? $categories->get($t->category_id) : null;
                if ($category) {
                    $add('c' . $category->id, $category->id, $category->name, $category->color, (float) $t->amount + $fee);
                } else {
                    $add('uncategorized', null, self::UNCATEGORIZED, 'gray', (float) $t->amount + $fee);
                }
            } else {
                // Fee dari transfer internal / alokasi tabungan
                $add('fee', null, self::FEE_CATEGORY, 'slate', $fee);
            }
        }

        return $groups;
    }

    protected function uncategorized(Collection $transactions): array
    {
        $rows = $transactions->filter(
            fn (Transaction $t) => $t->category_id === null && in_array($t->flowGroup(), [Transaction::FLOW_EXPENSE, Transaction::FLOW_INCOME], true)
        );

        return [
            'count'  => $rows->count(),
            'amount' => (float) $rows->sum(fn ($t) => (float) $t->amount),
        ];
    }

    protected function topExpenses(Collection $transactions, Collection $categories, Collection $savings, int $limit = 5): array
    {
        return $transactions
            ->filter(fn (Transaction $t) => $t->flowGroup() === Transaction::FLOW_EXPENSE)
            ->sortByDesc(fn ($t) => (float) $t->amount)
            ->take($limit)
            ->map(fn (Transaction $t) => [
                'id'          => $t->id,
                'description' => $t->description,
                'amount'      => (float) $t->amount + (float) ($t->fee ?? 0),
                'date'        => $t->transaction_date->toDateString(),
                'category'    => $t->category_id ? optional($categories->get($t->category_id))->name : null,
                'account'     => optional($savings->firstWhere('id', $t->savings_id))->name,
            ])
            ->values()
            ->all();
    }

    protected function accounts(Collection $savings, Collection $thisMonth): array
    {
        return $savings
            ->map(function (Saving $s) use ($thisMonth) {
                $change = (float) $thisMonth
                    ->where('savings_id', $s->id)
                    ->sum(fn (Transaction $t) => $t->balanceEffect());

                return [
                    'id'                => $s->id,
                    'name'              => $s->name,
                    'bank'              => optional($s->bank)->name,
                    'balance'           => (float) $s->current_balance,
                    'change_this_month' => $change,
                ];
            })
            ->sortByDesc('balance')
            ->values()
            ->all();
    }

    protected function budget(User $user, Carbon $now, Collection $thisMonthTx): array
    {
        $budget = \App\Models\Budget::with(['items.category'])
            ->where('user_id', $user->id)
            ->where('month', (int) $now->month)
            ->where('year', (int) $now->year)
            ->first();

        $rawItems = $budget ? $budget->items : collect([]);

        $items = $rawItems->map(function ($item) use ($thisMonthTx) {
            $spent = (float) $thisMonthTx
                ->filter(fn (Transaction $t) =>
                    $t->direction === Transaction::DIRECTION_OUT &&
                    $t->flowGroup() === Transaction::FLOW_EXPENSE &&
                    ($t->budget_item_id === $item->id || (!$t->budget_item_id && $t->category_id === $item->category_id))
                )
                ->sum(fn ($t) => (float) $t->amount + (float) ($t->fee ?? 0));

            $amount = (float) $item->amount;
            $pct = $amount > 0 ? round(($spent / $amount) * 100, 1) : 0;
            $status = $spent > $amount ? 'overbudget' : ($pct >= 80 ? 'warning' : 'safe');

            return [
                'id'         => $item->id,
                'name'       => $item->name ?? optional($item->category)->name,
                'amount'     => $amount,
                'spent'      => $spent,
                'percentage' => $pct,
                'status'     => $status,
            ];
        });

        $totalBudget = (float) $items->sum('amount');
        $totalSpent = (float) $items->sum('spent');
        $overallPercentage = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        $status = 'safe';
        if ($totalBudget > 0) {
            if ($totalSpent > $totalBudget) {
                $status = 'overbudget';
            } elseif ($overallPercentage >= 80) {
                $status = 'warning';
            }
        }

        return [
            'has_budget'  => $items->count() > 0,
            'total'       => $totalBudget,
            'spent'       => $totalSpent,
            'remaining'   => (float) ($totalBudget - $totalSpent),
            'percentage'  => $overallPercentage,
            'status'      => $status,
            'at_risk'     => $items->whereIn('status', ['warning', 'overbudget'])->sortByDesc('percentage')->values()->all(),
        ];
    }

    protected function monthlySeries(Collection $transactions, float $totalBalance, Carbon $windowStart, Carbon $now): array
    {
        $byMonth = [];
        foreach ($transactions as $t) {
            $m = $t->transaction_date->format('Y-m');
            $byMonth[$m] ??= [];
            $byMonth[$m][] = $t;
        }

        $labels = [];
        $income = [];
        $expense = [];
        $net = [];
        $netWorth = [];

        for ($i = 0; $i < 12; $i++) {
            $from = $windowStart->copy()->addMonthsNoOverflow($i);
            $mStr = $from->format('Y-m');
            $toStr = $from->copy()->endOfMonth()->toDateString();

            $flows = $this->summarizeFlows(collect($byMonth[$mStr] ?? []));

            $labels[] = $from->translatedFormat('M y');
            $income[] = $flows['income'];
            $expense[] = $flows['expense'];
            $net[] = $flows['income'] - $flows['expense'];

            $after = 0.0;
            foreach ($transactions as $t) {
                if ($t->transaction_date->toDateString() > $toStr) {
                    $after += $t->balanceEffect();
                }
            }
            $netWorth[] = $totalBalance - $after;
        }

        return compact('labels', 'income', 'expense', 'net') + ['net_worth' => $netWorth];
    }

    protected function dailyBalance(Collection $transactions, float $totalBalance, Carbon $today, int $days): array
    {
        $byDate = [];
        foreach ($transactions as $t) {
            $d = $t->transaction_date->toDateString();
            $byDate[$d] = ($byDate[$d] ?? 0.0) + $t->balanceEffect();
        }

        $labels = [];
        $data = [];
        $runningBalance = $totalBalance;

        for ($i = 0; $i < $days; $i++) {
            $day = $today->copy()->subDays($i);
            $dStr = $day->toDateString();

            $labels[] = $day->translatedFormat('d M');
            $data[] = $runningBalance;

            $runningBalance -= ($byDate[$dStr] ?? 0.0);
        }

        return [
            'labels' => array_reverse($labels),
            'data'   => array_reverse($data),
        ];
    }

    protected function dailyFlows(Collection $transactions, Carbon $today, int $days): array
    {
        $byDate = [];
        foreach ($transactions as $t) {
            $d = $t->transaction_date->toDateString();
            $byDate[$d] ??= [];
            $byDate[$d][] = $t;
        }

        $labels = [];
        $income = [];
        $expense = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $dStr = $day->toDateString();

            $flows = $this->summarizeFlows(collect($byDate[$dStr] ?? []));

            $labels[] = $day->translatedFormat('d M');
            $income[] = $flows['income'];
            $expense[] = $flows['expense'];
        }

        return ['labels' => $labels, 'cash_in' => $income, 'cash_out' => $expense];
    }

    protected function compare(float $current, float $previous): array
    {
        return [
            'amount'     => $current,
            'prev'       => $previous,
            'change'     => $current - $previous,
            'change_pct' => $this->pct($current - $previous, abs($previous)),
        ];
    }

    protected function pct(float $part, float $whole): ?float
    {
        return $whole > 0 ? round(($part / $whole) * 100, 1) : null;
    }
}


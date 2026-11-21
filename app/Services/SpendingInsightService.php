<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class SpendingInsightService
{
    public function build(User $user, ?Budget $activeBudget = null, ?float $cashInThisMonth = null, ?float $cashOutThisMonth = null): array
    {
        $now = now();
        $userId = $user->id;

        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // Single query untuk seluruh Cash Out (non-transfer) 2 bulan terakhir
        $txs = Transaction::with(['category:id,name', 'items.category:id,name'])
            ->where('user_id', $userId)
            ->whereNull('transfer_group_id')
            ->where('type', 'Cash Out')
            ->whereBetween('transaction_date', [$startOfLastMonth->toDateString(), $endOfThisMonth->toDateString()])
            ->get();

        $thisMonthPrefix = $now->format('Y-m');
        $lastMonthPrefix = $startOfLastMonth->format('Y-m');
        $todayStr = $now->toDateString();

        $dailyTotals = [];
        $currentByCat = [];
        $previousByCat = [];
        $computedThisMonthOut = 0.0;
        $spentToday = 0.0;

        foreach ($txs as $tx) {
            $txDateStr = $tx->transaction_date instanceof Carbon
                ? $tx->transaction_date->format('Y-m-d')
                : substr((string) $tx->transaction_date, 0, 10);

            $isThisMonth = str_starts_with($txDateStr, $thisMonthPrefix);
            $isLastMonth = str_starts_with($txDateStr, $lastMonthPrefix);

            $txAmount = (float) $tx->amount;

            if ($isThisMonth) {
                $computedThisMonthOut += $txAmount;
                $dailyTotals[$txDateStr] = ($dailyTotals[$txDateStr] ?? 0.0) + $txAmount;
                if ($txDateStr === $todayStr) {
                    $spentToday += $txAmount;
                }
            }

            // Breakdown per kategori (mendukung split transaksi)
            if ($tx->items->isNotEmpty()) {
                foreach ($tx->items as $sItem) {
                    $catName = $sItem->category?->name ?? 'Lainnya';
                    $partAmount = (float) $sItem->amount;
                    if ($isThisMonth) {
                        $currentByCat[$catName] = ($currentByCat[$catName] ?? 0.0) + $partAmount;
                    } elseif ($isLastMonth) {
                        $previousByCat[$catName] = ($previousByCat[$catName] ?? 0.0) + $partAmount;
                    }
                }
            } else {
                $catName = $tx->category?->name ?? 'Lainnya';
                if ($isThisMonth) {
                    $currentByCat[$catName] = ($currentByCat[$catName] ?? 0.0) + $txAmount;
                } elseif ($isLastMonth) {
                    $previousByCat[$catName] = ($previousByCat[$catName] ?? 0.0) + $txAmount;
                }
            }
        }

        // 1. Daily Safe-to-Spend
        $spentThisMonth = $cashOutThisMonth ?? $computedThisMonthOut;
        $budget = $activeBudget ?? Budget::with('items')
            ->where('user_id', $userId)
            ->where('month', (int) $now->month)
            ->where('year', (int) $now->year)
            ->first();

        $totalBudget = $budget ? (float) $budget->items->sum('amount') : 0.0;
        $daysInMonth = (int) $now->daysInMonth;
        $todayDay = (int) $now->day;
        $daysRemaining = max(1, $daysInMonth - $todayDay + 1);

        $hasBudget = $totalBudget > 0;
        if ($hasBudget) {
            $remainingPool = $totalBudget - $spentThisMonth;
            $poolLabel = 'Anggaran Bulan Ini';
            $poolTotal = $totalBudget;
        } else {
            $income = $cashInThisMonth ?? (float) Transaction::where('user_id', $userId)
                ->whereNull('transfer_group_id')
                ->where('type', 'Cash In')
                ->whereBetween('transaction_date', [$startOfThisMonth->toDateString(), $endOfThisMonth->toDateString()])
                ->sum('amount');
            $remainingPool = $income - $spentThisMonth;
            $poolLabel = 'Sisa Arus Kas Bulan Ini';
            $poolTotal = $income;
        }

        $dailySafe = $remainingPool > 0 ? round($remainingPool / $daysRemaining, 0) : 0.0;

        if ($remainingPool < 0) {
            $safeStatus = 'over';
        } elseif ($poolTotal > 0 && ($remainingPool / $poolTotal) <= 0.2) {
            $safeStatus = 'tight';
        } else {
            $safeStatus = 'safe';
        }

        $safeToSpend = [
            'has_budget'      => $hasBudget,
            'pool_label'      => $poolLabel,
            'pool_total'      => $poolTotal,
            'spent_month'     => $spentThisMonth,
            'remaining_pool'  => $remainingPool,
            'days_remaining'  => $daysRemaining,
            'daily_safe'      => $dailySafe,
            'spent_today'     => $spentToday,
            'status'          => $safeStatus,
            'today_over'      => $dailySafe > 0 && $spentToday > $dailySafe,
        ];

        // 2. MoM Spending Insights
        $allCategories = array_unique(array_merge(array_keys($currentByCat), array_keys($previousByCat)));
        $momItems = [];
        foreach ($allCategories as $catName) {
            $curr = $currentByCat[$catName] ?? 0.0;
            $prev = $previousByCat[$catName] ?? 0.0;
            $delta = $curr - $prev;
            $deltaPct = $prev > 0 ? round(($delta / $prev) * 100, 1) : null;

            if ($prev <= 0 && $curr > 0) {
                $trend = 'new';
            } elseif ($delta > 0) {
                $trend = 'up';
            } elseif ($delta < 0) {
                $trend = 'down';
            } else {
                $trend = 'flat';
            }

            $momItems[] = [
                'category'        => $catName,
                'current_amount'  => $curr,
                'previous_amount' => $prev,
                'delta_amount'    => $delta,
                'delta_pct'       => $deltaPct,
                'trend'           => $trend,
            ];
        }

        usort($momItems, fn($a, $b) => $b['current_amount'] <=> $a['current_amount']);

        $topSpike = collect($momItems)
            ->filter(fn($it) => $it['delta_amount'] > 0 && ($it['delta_pct'] === null || $it['delta_pct'] >= 15))
            ->sortByDesc('delta_amount')
            ->first();

        $biggestSaving = collect($momItems)
            ->filter(fn($it) => $it['delta_amount'] < 0)
            ->sortBy('delta_amount')
            ->first();

        // 3. Spending Heatmap & No-Spend Day Tracker
        $elapsedDays = max(1, $todayDay);
        $avgDailySpend = $spentThisMonth / $elapsedDays;
        $scaleRef = $dailySafe > 0 ? $dailySafe : ($avgDailySpend > 0 ? $avgDailySpend : 100000.0);

        $calendarDays = [];
        $noSpendDays = 0;
        $maxDay = null;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateObj = $startOfThisMonth->copy()->day($d);
            $dStr = $dateObj->toDateString();
            $amt = $dailyTotals[$dStr] ?? 0.0;
            $isFuture = $d > $todayDay;
            $isToday = $d === $todayDay;

            if (!$isFuture && $amt <= 0) {
                $noSpendDays++;
            }

            if (!$isFuture && $amt > 0 && ($maxDay === null || $amt > $maxDay['amount'])) {
                $maxDay = ['date' => $dStr, 'day' => $d, 'amount' => $amt];
            }

            if ($isFuture || $amt <= 0) {
                $level = 0;
            } elseif ($amt <= $scaleRef * 0.5) {
                $level = 1;
            } elseif ($amt <= $scaleRef) {
                $level = 2;
            } elseif ($amt <= $scaleRef * 1.5) {
                $level = 3;
            } else {
                $level = 4;
            }

            $calendarDays[] = [
                'date'      => $dStr,
                'day'       => $d,
                'amount'    => $amt,
                'is_future' => $isFuture,
                'is_today'  => $isToday,
                'level'     => $level,
            ];
        }

        // Hitung streak No-Spend Day berturut-turut sampai hari ini
        $streak = 0;
        for ($d = $todayDay; $d >= 1; $d--) {
            $dStr = $startOfThisMonth->copy()->day($d)->toDateString();
            if (($dailyTotals[$dStr] ?? 0.0) <= 0) {
                $streak++;
            } else {
                break;
            }
        }

        $heatmap = [
            'month_label'          => $now->translatedFormat('F Y'),
            'first_weekday_offset' => (int) $startOfThisMonth->dayOfWeekIso - 1, // 0 = Senin .. 6 = Minggu
            'days'                 => $calendarDays,
            'no_spend_days'        => $noSpendDays,
            'elapsed_days'         => $elapsedDays,
            'no_spend_streak'      => $streak,
            'max_day'              => $maxDay,
        ];

        return [
            'safe_to_spend' => $safeToSpend,
            'mom_insights'  => [
                'items'          => array_slice($momItems, 0, 6),
                'top_spike'      => $topSpike,
                'biggest_saving' => $biggestSaving,
                'last_month_label' => $startOfLastMonth->translatedFormat('F'),
            ],
            'heatmap'       => $heatmap,
        ];
    }
}

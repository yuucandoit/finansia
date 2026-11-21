<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Debt;
use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\RecurringTransaction;
use App\Models\User;

class NotificationService
{
    public function getUserNotifications(User $user): array
    {
        $notifications = [];
        $today = now()->startOfDay();
        $todayStr = $today->toDateString();
        $soonStr = $today->copy()->addDays(3)->toDateString();

        // 1. Anggaran Bulanan (Over-budget >= 100% & Warning >= 80%)
        $budget = Budget::with('items')
            ->where('user_id', $user->id)
            ->where('month', (int) now()->month)
            ->where('year', (int) now()->year)
            ->first();

        if ($budget) {
            foreach ($budget->items as $bItem) {
                if ((float) $bItem->amount <= 0) {
                    continue;
                }
                if ($bItem->spent > $bItem->amount) {
                    $notifications[] = [
                        'id'       => 'budget-over-' . $bItem->id,
                        'category' => 'Anggaran',
                        'severity' => 'danger',
                        'icon'     => '🚨',
                        'title'    => "Over-Budget: {$bItem->name}",
                        'message'  => "Pemakaian {$bItem->percentage}% (Rp " . number_format($bItem->spent, 0, ',', '.') . " / Rp " . number_format($bItem->amount, 0, ',', '.') . ")",
                        'url'      => route('budget.index'),
                    ];
                } elseif ($bItem->percentage >= 80) {
                    $notifications[] = [
                        'id'       => 'budget-warn-' . $bItem->id,
                        'category' => 'Anggaran',
                        'severity' => 'warning',
                        'icon'     => '⚠️',
                        'title'    => "Anggaran Hampir Habis: {$bItem->name}",
                        'message'  => "Sudah terpakai {$bItem->percentage}% dari batas bulan ini.",
                        'url'      => route('budget.index'),
                    ];
                }
            }
        }

        // 2. Transaksi Rutin Jatuh Tempo (<= 3 hari ke depan)
        $dueRecurrings = RecurringTransaction::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('next_run_date', '<=', $soonStr)
            ->orderBy('next_run_date')
            ->get();

        foreach ($dueRecurrings as $rec) {
            $isDueTodayOrPast = $rec->next_run_date->format('Y-m-d') <= $todayStr;
            $notifications[] = [
                'id'       => 'rec-' . $rec->id,
                'category' => 'Transaksi Rutin',
                'severity' => $isDueTodayOrPast ? 'warning' : 'info',
                'icon'     => '🔁',
                'title'    => ($isDueTodayOrPast ? 'Tagihan Rutin Jatuh Tempo: ' : 'Tagihan Rutin Mendatang: ') . $rec->description,
                'message'  => "Rp " . number_format((float) $rec->amount, 0, ',', '.') . " • Jadwal: " . $rec->next_run_date->format('d M Y'),
                'url'      => route('recurring.index'),
            ];
        }

        // 3. Hutang & Piutang Jatuh Tempo (<= 3 hari ke depan)
        $dueDebts = Debt::where('user_id', $user->id)
            ->where('status', '!=', Debt::STATUS_PAID)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $soonStr)
            ->orderBy('due_date')
            ->get();

        foreach ($dueDebts as $debt) {
            $isOverdue = $debt->is_overdue;
            $label = $debt->type === Debt::TYPE_PAYABLE ? 'Hutang' : 'Piutang';
            $notifications[] = [
                'id'       => 'debt-' . $debt->id,
                'category' => 'Hutang & Piutang',
                'severity' => $isOverdue ? 'danger' : 'warning',
                'icon'     => '🧾',
                'title'    => ($isOverdue ? "{$label} Lewat Jatuh Tempo: " : "{$label} Segera Jatuh Tempo: ") . $debt->person_name,
                'message'  => "Sisa Rp " . number_format($debt->remaining_amount, 0, ',', '.') . " • Tempo: " . $debt->due_date->format('d M Y'),
                'url'      => route('debt.index'),
            ];
        }

        // 4. Future Plan Gantt Chart Overdue / H-3
        $accessiblePlanIds = FuturePlan::accessibleBy($user->id)->pluck('id');
        if ($accessiblePlanIds->isNotEmpty()) {
            $duePlanItems = FuturePlanItem::with('futurePlan:id,title')
                ->whereIn('future_plan_id', $accessiblePlanIds)
                ->where('is_completed', false)
                ->whereNotNull('end_date')
                ->whereDate('end_date', '<=', $soonStr)
                ->orderBy('end_date')
                ->limit(10)
                ->get();

            foreach ($duePlanItems as $pItem) {
                $isOverdue = $pItem->end_date->format('Y-m-d') < $todayStr;
                $notifications[] = [
                    'id'       => 'fp-item-' . $pItem->id,
                    'category' => 'Future Planner',
                    'severity' => $isOverdue ? 'danger' : 'warning',
                    'icon'     => $isOverdue ? '⏰' : '📅',
                    'title'    => ($isOverdue ? 'Item Rencana Overdue: ' : 'Deadline Rencana Dekat: ') . $pItem->title,
                    'message'  => ($pItem->futurePlan->title ?? 'Future Plan') . " • Target: " . $pItem->end_date->format('d M Y'),
                    'url'      => route('future-planner.show', $pItem->future_plan_id),
                ];
            }
        }

        return $notifications;
    }
}

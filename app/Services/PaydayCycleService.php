<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class PaydayCycleService
{
    /**
     * Hitung tanggal payday pada bulan & tahun tertentu (aman untuk tgl 29-31 & penyesuaian akhir pekan).
     */
    public function resolvePaydayDate(int $year, int $month, int $paydayDay, bool $adjustWeekend = false): Carbon
    {
        $clampedDay = max(1, min(31, $paydayDay));
        $base = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $day = min($clampedDay, $base->daysInMonth);
        $date = $base->copy()->day($day);

        if ($adjustWeekend) {
            if ($date->isSaturday()) {
                $date->subDay();
            } elseif ($date->isSunday()) {
                $date->subDays(2);
            }
        }

        return $date;
    }

    /**
     * Ambil rentang siklus Payday-to-Payday untuk offset tertentu (0 = siklus aktif, -1 = siklus lalu, dst).
     */
    public function getCycleForOffset(User $user, int $offset = 0, ?Carbon $reference = null): array
    {
        $ref = ($reference ? $reference->copy() : now())->startOfDay();
        $paydayDay = max(1, min(31, (int) ($user->payday_day ?? 1)));
        $adjustWeekend = (bool) ($user->payday_weekend_adjust ?? false);

        // Tentukan bulan anchor siklus aktif saat ini
        $currentMonthPayday = $this->resolvePaydayDate($ref->year, $ref->month, $paydayDay, $adjustWeekend);

        if ($ref->lt($currentMonthPayday)) {
            $anchor = $ref->copy()->subMonthNoOverflow()->startOfMonth();
        } else {
            $anchor = $ref->copy()->startOfMonth();
        }

        if ($offset !== 0) {
            $anchor->addMonthsNoOverflow($offset);
        }

        $start = $this->resolvePaydayDate($anchor->year, $anchor->month, $paydayDay, $adjustWeekend);
        $nextMonth = $anchor->copy()->addMonthNoOverflow();
        $nextPayday = $this->resolvePaydayDate($nextMonth->year, $nextMonth->month, $paydayDay, $adjustWeekend);
        $end = $nextPayday->copy()->subDay()->endOfDay();

        $daysTotal = max(1, (int) $start->diffInDays($nextPayday));
        $daysRemaining = $ref->betweenIncluded($start, $end)
            ? max(1, (int) $ref->diffInDays($nextPayday))
            : 0;

        $label = $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M Y');

        return [
            'offset'           => $offset,
            'payday_day'       => $paydayDay,
            'is_custom_payday' => $paydayDay > 1 || $adjustWeekend,
            'start'            => $start,
            'end'              => $end,
            'next_payday'      => $nextPayday,
            'days_total'       => $daysTotal,
            'days_remaining'   => $daysRemaining,
            'label'            => $label,
        ];
    }

    /**
     * Daftar pilihan siklus P2P terbaru untuk dropdown filter.
     */
    public function getAvailableCycles(User $user, int $count = 6, ?Carbon $reference = null): array
    {
        $cycles = [];
        for ($i = 0; $i > -$count; $i--) {
            $cycle = $this->getCycleForOffset($user, $i, $reference);
            $prefix = match ($i) {
                0       => 'Siklus Aktif',
                -1      => 'Siklus Lalu',
                default => abs($i) . ' Siklus Lalu',
            };

            $cycles[] = [
                'key'    => (string) $i,
                'offset' => $i,
                'label'  => "{$prefix} ({$cycle['label']})",
                'short'  => $cycle['label'],
                'start'  => $cycle['start']->toDateString(),
                'end'    => $cycle['end']->toDateString(),
            ];
        }

        return $cycles;
    }
}

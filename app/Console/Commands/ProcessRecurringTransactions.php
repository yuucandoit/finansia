<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'finance:process-recurring';

    protected $description = 'Memproses transaksi berulang yang sudah jatuh tempo hari ini';

    public function handle(): int
    {
        $today = now()->toDateString();

        $dueRecurrings = RecurringTransaction::where('is_active', true)
            ->whereDate('next_run_date', '<=', $today)
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($dueRecurrings as $recurring) {
            try {
                $recurring->executeNow(now());
                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Gagal memproses ID {$recurring->id}: " . $e->getMessage());
            }
        }

        $this->info("Selesai: {$processed} transaksi berulang diproses, {$failed} gagal.");

        return self::SUCCESS;
    }
}

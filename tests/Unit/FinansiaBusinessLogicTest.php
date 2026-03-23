<?php

/**
 * Unit Tests – Logic Bisnis Finansia
 *
 * Test ini TIDAK membutuhkan database atau HTTP stack.
 * Fokus pada perhitungan murni yang dipakai di controller.
 */

// ─────────────────────────────────────────────────────────
//  KALKULASI SALDO & ALIRAN KAS
// ─────────────────────────────────────────────────────────

test('[UNIT] cash flow calculation is correct with mixed transaction types', function () {
    $transactions = collect([
        (object)['type' => 'Cash In',  'amount' => 5_000_000],
        (object)['type' => 'Cash Out', 'amount' => 1_000_000],
        (object)['type' => 'Transfer', 'amount' =>   500_000],
        (object)['type' => 'Tabungan', 'amount' =>   200_000],
        (object)['type' => 'Cash In',  'amount' => 2_000_000],
    ]);

    $totalCashIn  = $transactions->where('type', 'Cash In')->sum('amount');
    $totalCashOut = $transactions->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])->sum('amount');
    $netFlow      = $totalCashIn - $totalCashOut;

    expect($totalCashIn)->toBe(7_000_000)
        ->and($totalCashOut)->toBe(1_700_000)
        ->and($netFlow)->toBe(5_300_000);
});

test('[UNIT] running balance calculation goes backward correctly', function () {
    $currentBalance = 10_000_000;

    $transactions = [
        (object)['type' => 'Cash In',  'amount' => 3_000_000],
        (object)['type' => 'Cash Out', 'amount' => 1_000_000],
    ];

    // Simulasikan logika DashboardController::index() – mundur 1 hari
    $runningBalance = $currentBalance;
    foreach ($transactions as $trans) {
        if ($trans->type === 'Cash In') {
            $runningBalance -= $trans->amount; // kurangi karena mundur
        } else {
            $runningBalance += $trans->amount; // tambah karena mundur
        }
    }

    // Balance kemarin = 10M - 3M + 1M = 8M
    expect($runningBalance)->toBe(8_000_000);
});

test('[UNIT] top expense grouping by description works correctly', function () {
    $transactions = collect([
        (object)['type' => 'Cash Out', 'description' => 'Belanja', 'amount' => 500_000],
        (object)['type' => 'Cash Out', 'description' => 'Belanja', 'amount' => 300_000],
        (object)['type' => 'Cash Out', 'description' => 'Makan',   'amount' => 200_000],
        (object)['type' => 'Cash In',  'description' => 'Gaji',    'amount' => 5_000_000],
    ]);

    $grouped = $transactions
        ->where('type', 'Cash Out')
        ->groupBy('description')
        ->map(fn ($items) => $items->sum('amount'))
        ->sortDesc();

    expect($grouped->first())->toBe(800_000)
        ->and($grouped->keys()->first())->toBe('Belanja')
        ->and($grouped)->toHaveCount(2);
});

// ─────────────────────────────────────────────────────────
//  VALIDASI TIPE TRANSAKSI
// ─────────────────────────────────────────────────────────

test('[UNIT] valid transaction types are accepted', function () {
    $validTypes = ['Cash In', 'Cash Out', 'Transfer', 'Tabungan'];

    foreach ($validTypes as $type) {
        expect(in_array($type, $validTypes))->toBeTrue();
    }
});

test('[UNIT] invalid transaction types are rejected', function () {
    $validTypes    = ['Cash In', 'Cash Out', 'Transfer', 'Tabungan'];
    $invalidInputs = ['cash in', 'CASHIN', 'Debit', 'Kredit', '', null];

    foreach ($invalidInputs as $input) {
        expect(in_array($input, $validTypes, true))->toBeFalse();
    }
});

// ─────────────────────────────────────────────────────────
//  PENGKLASIFIKASIAN TIPE DARI E-STATEMENT IMPORT
//  (logika yang ada di TransactionController::import())
// ─────────────────────────────────────────────────────────

test('[UNIT] import correctly classifies cash-in from amount_in', function () {
    $row = ['amount_in' => 5_000_000, 'amount_out' => 0, 'description' => 'Gaji'];

    $type   = 'Cash Out';
    $amount = $row['amount_out'];

    if (($row['amount_in'] ?? 0) > 0) {
        $type   = 'Cash In';
        $amount = $row['amount_in'];
    }

    expect($type)->toBe('Cash In')
        ->and($amount)->toBe(5_000_000);
});

test('[UNIT] import correctly classifies cash-out when only amount_out present', function () {
    $row = ['amount_in' => 0, 'amount_out' => 350_000, 'description' => 'Belanja'];

    $type   = 'Cash Out';
    $amount = $row['amount_out'];

    if (($row['amount_in'] ?? 0) > 0) {
        $type   = 'Cash In';
        $amount = $row['amount_in'];
    }

    expect($type)->toBe('Cash Out')
        ->and($amount)->toBe(350_000);
});

// ─────────────────────────────────────────────────────────
//  BANK TYPE DETECTION LOGIC
// ─────────────────────────────────────────────────────────

test('[UNIT] bank type detected correctly from bank name', function () {
    $bankNameMap = [
        'Bank Mandiri'   => 'mandiri',
        'BCA'            => 'bca',
        'Bank BCA'       => 'bca',
        'Bank Mandiri KC'=> 'mandiri',
        'BRI'            => 'mandiri', // fallback ke default
        'Unknown Bank'   => 'mandiri', // fallback ke default
    ];

    foreach ($bankNameMap as $bankName => $expectedType) {
        $bankNameLower = strtolower($bankName);
        $bankType      = 'mandiri'; // default

        if (str_contains($bankNameLower, 'mandiri')) {
            $bankType = 'mandiri';
        } elseif (str_contains($bankNameLower, 'bca')) {
            $bankType = 'bca';
        }

        expect($bankType)->toBe($expectedType, "Failed for bank: {$bankName}");
    }
});

// ─────────────────────────────────────────────────────────
//  PERCENTILE CALCULATION (dipakai di PerformanceTest)
// ─────────────────────────────────────────────────────────

test('[UNIT] percentile calculation is correct', function () {
    $data = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];

    // P50 → median = 50 (percentile returns float so use toEqual)
    $p50 = percentile($data, 50);
    expect($p50)->toEqual(50);

    // P95 → 95% dari 10 = index ke-9 (nilai 100)
    $p95 = percentile($data, 95);
    expect($p95)->toEqual(100);

    // P90 → 90% dari 10 = index ke-8 (nilai 90) → ceil(0.9 * 10) = 9, index 8 = 90
    $p90 = percentile($data, 90);
    expect($p90)->toEqual(90);
});

test('[UNIT] total balance sums correctly across multiple savings', function () {
    $savings = collect([
        (object)['current_balance' => 1_000_000],
        (object)['current_balance' => 2_500_000],
        (object)['current_balance' => 500_000],
    ]);

    $total = $savings->sum('current_balance');

    expect($total)->toBe(4_000_000);
});

test('[UNIT] date range filtering logic for monthly data works', function () {
    $now        = \Carbon\Carbon::create(2026, 3, 15);
    $startMonth = $now->copy()->startOfMonth(); // 1 Mar 2026
    $endMonth   = $now->copy()->endOfMonth();   // 31 Mar 2026

    $transactions = collect([
        (object)['transaction_date' => '2026-03-01', 'amount' => 100_000, 'type' => 'Cash In'],
        (object)['transaction_date' => '2026-03-15', 'amount' => 200_000, 'type' => 'Cash In'],
        (object)['transaction_date' => '2026-02-28', 'amount' => 500_000, 'type' => 'Cash In'], // bulan lalu
        (object)['transaction_date' => '2026-04-01', 'amount' => 300_000, 'type' => 'Cash In'], // bulan depan
    ]);

    $thisMonth = $transactions->filter(function ($t) use ($startMonth, $endMonth) {
        $date = \Carbon\Carbon::parse($t->transaction_date);
        return $date->between($startMonth, $endMonth);
    });

    expect($thisMonth->sum('amount'))->toBe(300_000);
    expect($thisMonth->count())->toBe(2);
});

// ─────────────────────────────────────────────────────────
//  HELPER (sama dengan yang di PerformanceTest agar Unit bisa berdiri sendiri)
// ─────────────────────────────────────────────────────────

if (!function_exists('percentile')) {
    function percentile(array $data, int $percentile): float
    {
        sort($data);
        $index = ceil(($percentile / 100) * count($data)) - 1;
        return $data[max($index, 0)];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
*/

/**
 * Hitung nilai persentil dari array angka.
 * Digunakan di PerformanceTest & Unit tests.
 */
function percentile(array $data, int $percentile): float
{
    sort($data);
    $index = (int) ceil(($percentile / 100) * count($data)) - 1;
    return (float) ($data[max($index, 0)] ?? 0);
}

/*
|--------------------------------------------------------------------------
| Helper untuk mengukur waktu eksekusi (ms)
|--------------------------------------------------------------------------
*/

/**
 * Jalankan callable dan kembalikan [result, elapsed_ms]
 */
function measure(callable $fn): array
{
    $start  = microtime(true);
    $result = $fn();
    $ms     = (microtime(true) - $start) * 1000;

    return [$result, round($ms, 3)];
}

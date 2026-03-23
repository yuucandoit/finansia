<?php

/**
 * ═══════════════════════════════════════════════════════
 *  STRESS TEST  &  ENDURANCE / DURABILITY TEST
 *  Finansia Laravel Application
 *
 *  Kelompok test ini TIDAK menggunakan RefreshDatabase:
 *  - Setiap test membuat data banyak untuk mensimulasikan
 *    beban nyata di production.
 *  - Jalankan dengan database test yang sudah dikonfigurasi
 *    di phpunit.xml (DB_DATABASE=finansia_test).
 * ═══════════════════════════════════════════════════════
 */

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────
//  GROUP 1: STRESS TEST – Lonjakan Pengguna Bersamaan
//  Mensimulasikan akses bersamaan via loop sequential
// ─────────────────────────────────────────────────────────

test('[STRESS] dashboard handles 50 sequential rapid requests within 5 seconds', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(200)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    $totalStart = microtime(true);
    $results    = [];

    for ($i = 0; $i < 50; $i++) {
        $start    = microtime(true);
        $response = $this->actingAs($user)->get('/dashboard');
        $elapsed  = (microtime(true) - $start) * 1000;

        $results[] = $elapsed;

        expect($response->status())->toBe(200);
    }

    $totalElapsed = (microtime(true) - $totalStart) * 1000;
    $avgMs        = array_sum($results) / count($results);
    $maxMs        = max($results);
    $p95          = percentile($results, 95);

    Log::info('[STRESS] Dashboard 50-req results', [
        'total_ms'   => round($totalElapsed, 2),
        'average_ms' => round($avgMs, 2),
        'max_ms'     => round($maxMs, 2),
        'p95_ms'     => round($p95, 2),
    ]);

    // Total 50 request harus selesai < 5 detik (5000ms)
    expect($totalElapsed)->toBeLessThan(5000);
    // Rata-rata per request < 200ms
    expect($avgMs)->toBeLessThan(200);
})->group('stress');

test('[STRESS] transaction list handles 30 concurrent users (simulated)', function () {
    $results = [];

    for ($i = 0; $i < 30; $i++) {
        $user   = User::factory()->create();
        $saving = Saving::factory()->create(['user_id' => $user->id]);
        Transaction::factory(100)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

        $start    = microtime(true);
        $response = $this->actingAs($user)->get('/transaksi');
        $elapsed  = (microtime(true) - $start) * 1000;

        $results[] = $elapsed;
        expect($response->status())->toBe(200);
    }

    $avgMs = array_sum($results) / count($results);
    $p95   = percentile($results, 95);

    Log::info('[STRESS] Transaction 30-user results', [
        'average_ms' => round($avgMs, 2),
        'p95_ms'     => round($p95, 2),
        'max_ms'     => round(max($results), 2),
    ]);

    expect($avgMs)->toBeLessThan(300);
    expect($p95)->toBeLessThan(500);
})->group('stress');

// ─────────────────────────────────────────────────────────
//  GROUP 2: LOAD TEST – Data Besar
//  Mensimulasikan pengguna dengan riwayat transaksi panjang
// ─────────────────────────────────────────────────────────

test('[LOAD] dashboard stays fast with 1000 transactions', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(1000)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    $start    = microtime(true);
    $response = $this->actingAs($user)->get('/dashboard');
    $elapsed  = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);

    Log::info('[LOAD] Dashboard with 1000 txns', ['elapsed_ms' => round($elapsed, 2)]);

    // Harus selesai < 1 detik
    expect($elapsed)->toBeLessThan(1000);
})->group('load');

test('[LOAD] transaction list paginates correctly with 5000 records', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    // Insert batch untuk efisiensi
    $chunks = collect(range(1, 5000))->chunk(500);
    foreach ($chunks as $chunk) {
        $rows = $chunk->map(fn ($i) => [
            'user_id'          => $user->id,
            'savings_id'       => $saving->id,
            'type'             => $i % 2 === 0 ? 'Cash In' : 'Cash Out',
            'description'      => "Transaksi #{$i}",
            'amount'           => rand(10000, 500000),
            'is_manual'        => true,
            'transaction_date' => now()->subDays(rand(0, 365))->toDateTimeString(),
            'created_at'       => now()->toDateTimeString(),
            'updated_at'       => now()->toDateTimeString(),
        ])->toArray();

        DB::table('transactions')->insert($rows);
    }

    $start    = microtime(true);
    $response = $this->actingAs($user)->get('/transaksi?page=1');
    $elapsed  = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);
    $viewData = $response->viewData('transaction');

    Log::info('[LOAD] Transaction 5000 records', [
        'elapsed_ms' => round($elapsed, 2),
        'total'      => $viewData->total(),
        'per_page'   => $viewData->perPage(),
    ]);

    expect($viewData->total())->toBe(5000);
    expect($viewData->perPage())->toBe(15);
    expect($elapsed)->toBeLessThan(800);
})->group('load');

test('[LOAD] saving detail with heavy transaction history stays responsive', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $rows = collect(range(1, 2000))->map(fn ($i) => [
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => $i % 3 === 0 ? 'Cash In' : 'Cash Out',
        'description'      => "Transaksi #{$i}",
        'amount'           => rand(50000, 5000000),
        'is_manual'        => true,
        'transaction_date' => now()->subDays(rand(0, 730))->toDateTimeString(),
        'created_at'       => now()->toDateTimeString(),
        'updated_at'       => now()->toDateTimeString(),
    ])->toArray();

    DB::table('transactions')->insert($rows);

    $start    = microtime(true);
    $response = $this->actingAs($user)->get("/saving/detail/{$saving->id}");
    $elapsed  = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);
    Log::info('[LOAD] Saving detail 2000 txns', ['elapsed_ms' => round($elapsed, 2)]);

    expect($elapsed)->toBeLessThan(600);
})->group('load');

// ─────────────────────────────────────────────────────────
//  GROUP 3: ENDURANCE TEST – Banyak Request Berulang
//  Deteksi memory leak & degradasi performa seiring waktu
// ─────────────────────────────────────────────────────────

test('[ENDURANCE] repeated dashboard access does not degrade response time', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(200)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    $measurements = [];
    $memoryUsage  = [];

    for ($i = 0; $i < 100; $i++) {
        $start    = microtime(true);
        $response = $this->actingAs($user)->get('/dashboard');
        $elapsed  = (microtime(true) - $start) * 1000;

        $measurements[] = $elapsed;
        $memoryUsage[]  = memory_get_usage(true) / 1024 / 1024; // MB

        expect($response->status())->toBe(200);
    }

    // Bandingkan 10 request pertama vs 10 request terakhir
    $firstTen = array_slice($measurements, 0, 10);
    $lastTen  = array_slice($measurements, -10);
    $avgFirst = array_sum($firstTen) / count($firstTen);
    $avgLast  = array_sum($lastTen) / count($lastTen);

    // Memory tidak boleh naik lebih dari 20MB dalam 100 request
    $memoryGrowth = end($memoryUsage) - $memoryUsage[0];

    Log::info('[ENDURANCE] Dashboard 100-req degradation', [
        'avg_first_10_ms' => round($avgFirst, 2),
        'avg_last_10_ms'  => round($avgLast, 2),
        'degradation_pct' => round((($avgLast - $avgFirst) / $avgFirst) * 100, 1) . '%',
        'memory_growth_mb'=> round($memoryGrowth, 2),
        'peak_memory_mb'  => round(max($memoryUsage), 2),
    ]);

    // Degradasi tidak boleh lebih dari 100% (dua kali lipat)
    expect($avgLast)->toBeLessThan($avgFirst * 2);
    // Memory growth tidak lebih dari 20MB
    expect($memoryGrowth)->toBeLessThan(20);
})->group('endurance');

test('[ENDURANCE] transaction store under repeated writes stays consistent', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);

    $measurements = [];

    for ($i = 0; $i < 50; $i++) {
        $payload = [
            'description'      => "Transaksi Endurance #{$i}",
            'type'             => $i % 2 === 0 ? 'Cash In' : 'Cash Out',
            'amount'           => rand(10000, 1000000),
            'savings_id'       => $saving->id,
            'transaction_date' => now()->toDateString(),
        ];

        $start    = microtime(true);
        $response = $this->actingAs($user)->post('/transaksi/store', $payload);
        $elapsed  = (microtime(true) - $start) * 1000;

        $measurements[] = $elapsed;
        $response->assertRedirect(route('transaction.index'));
    }

    $avgMs   = array_sum($measurements) / count($measurements);
    $maxMs   = max($measurements);

    Log::info('[ENDURANCE] Transaction write 50 times', [
        'average_ms' => round($avgMs, 2),
        'max_ms'     => round($maxMs, 2),
    ]);

    // Semua 50 transaksi tersimpan
    expect(Transaction::where('user_id', $user->id)->count())->toBe(50);
    // Rata-rata write < 200ms
    expect($avgMs)->toBeLessThan(200);
})->group('endurance');

// ─────────────────────────────────────────────────────────
//  GROUP 4: SPIKE TEST – Lonjakan mendadak dari baseline
// ─────────────────────────────────────────────────────────

test('[SPIKE] application recovers after sudden burst of requests', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(100)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    // Baseline: 5 request normal
    $baselineResults = [];
    for ($i = 0; $i < 5; $i++) {
        $start = microtime(true);
        $this->actingAs($user)->get('/dashboard');
        $baselineResults[] = (microtime(true) - $start) * 1000;
    }
    $baselineAvg = array_sum($baselineResults) / count($baselineResults);

    // SPIKE: 20 request sekaligus (simulasi)
    for ($i = 0; $i < 20; $i++) {
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);
    }

    // Post-spike: 5 request harus kembali normal
    $postSpikeResults = [];
    for ($i = 0; $i < 5; $i++) {
        $start = microtime(true);
        $this->actingAs($user)->get('/dashboard');
        $postSpikeResults[] = (microtime(true) - $start) * 1000;
    }
    $postSpikeAvg = array_sum($postSpikeResults) / count($postSpikeResults);

    Log::info('[SPIKE] Spike test results', [
        'baseline_avg_ms'   => round($baselineAvg, 2),
        'post_spike_avg_ms' => round($postSpikeAvg, 2),
    ]);

    // Post-spike tidak boleh lebih dari 3x baseline
    expect($postSpikeAvg)->toBeLessThan($baselineAvg * 3);
})->group('spike');

// ─────────────────────────────────────────────────────────
//  GROUP 5: DATABASE QUERY EFFICIENCY TEST
// ─────────────────────────────────────────────────────────

test('[DB] dashboard tidak menjalankan terlalu banyak query (N+1 check)', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(50)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    $queries = [];
    DB::listen(fn ($q) => $queries[] = $q->sql);

    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertStatus(200);

    $queryCount = count($queries);

    Log::info('[DB] Dashboard query count', [
        'count'   => $queryCount,
        'queries' => array_slice($queries, 0, 20), // log pertama 20
    ]);

    // Dashboard seharusnya tidak lebih dari 30 query dengan data moderate
    expect($queryCount)->toBeLessThan(30);
})->group('database');

test('[DB] transaction list tidak menjalankan N+1 query', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(50)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    $queries = [];
    DB::listen(fn ($q) => $queries[] = $q->sql);

    $response = $this->actingAs($user)->get('/transaksi');
    $response->assertStatus(200);

    $queryCount = count($queries);
    Log::info('[DB] Transaction query count', ['count' => $queryCount]);

    expect($queryCount)->toBeLessThan(20);
})->group('database');

// ─────────────────────────────────────────────────────────
//  GROUP 6: SOAK TEST – Simulasi akses panjang (100 req)
//  Biasanya dilakukan berjam-jam; ini versi accelerated
// ─────────────────────────────────────────────────────────

test('[SOAK] application stays healthy under 100 sequential page requests', function () {
    $user   = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id]);
    Transaction::factory(100)->create(['user_id' => $user->id, 'savings_id' => $saving->id]);

    $errors      = 0;
    $responseTimes = [];

    $routes = [
        '/dashboard',
        '/transaksi',
        '/saving',
        "/saving/detail/{$saving->id}",
    ];

    for ($i = 0; $i < 100; $i++) {
        $route    = $routes[$i % count($routes)];
        $start    = microtime(true);
        $response = $this->actingAs($user)->get($route);
        $elapsed  = (microtime(true) - $start) * 1000;

        $responseTimes[] = $elapsed;

        if ($response->status() !== 200) {
            $errors++;
        }
    }

    $errorRate = ($errors / 100) * 100;
    $avgMs     = array_sum($responseTimes) / count($responseTimes);
    $p95       = percentile($responseTimes, 95);

    Log::info('[SOAK] 100-request soak results', [
        'error_rate_pct' => $errorRate,
        'average_ms'     => round($avgMs, 2),
        'p95_ms'         => round($p95, 2),
        'max_ms'         => round(max($responseTimes), 2),
        'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
    ]);

    // Error rate = 0% (semua request berhasil)
    expect($errorRate)->toEqual(0); // int(0) atau float(0.0) keduanya valid
    expect($avgMs)->toBeLessThan(300);
})->group('soak');


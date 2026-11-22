<?php

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaydayCycleService;
use App\Services\SpendingInsightService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payday cycle service clamps end-of-month dates and adjusts weekends properly', function () {
    $service = app(PaydayCycleService::class);

    // 31 in Feb 2026 (non-leap) -> Feb 28
    $febDate = $service->resolvePaydayDate(2026, 2, 31, false);
    expect($febDate->format('Y-m-d'))->toBe('2026-02-28');

    // Weekend adjust: 2026-11-22 is Sunday -> shifts to Friday 2026-11-20
    $sundayAdjusted = $service->resolvePaydayDate(2026, 11, 22, true);
    expect($sundayAdjusted->format('Y-m-d'))->toBe('2026-11-20');
});

test('user can update payday settings in profile and filter transactions by p2p cycle', function () {
    $user = User::factory()->create(['payday_day' => 1]);
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);

    // Set payday to 25th
    $this->actingAs($user)
        ->patch(route('profile.payday.update'), [
            'payday_day' => 25,
            'payday_weekend_adjust' => 0,
        ])
        ->assertRedirect();

    expect($user->fresh()->payday_day)->toBe(25);

    $service = app(PaydayCycleService::class);
    $currentCycle = $service->getCycleForOffset($user->fresh(), 0);
    $prevCycle = $service->getCycleForOffset($user->fresh(), -1);

    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash Out',
        'amount' => 100_000,
        'description' => 'Belanja Siklus Aktif',
        'transaction_date' => $currentCycle['start']->toDateString(),
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'type' => 'Cash Out',
        'amount' => 200_000,
        'description' => 'Belanja Siklus Lalu',
        'transaction_date' => $prevCycle['start']->toDateString(),
    ]);

    // Filter active P2P cycle (0)
    $this->actingAs($user)
        ->get('/transaksi?cycle=0')
        ->assertStatus(200)
        ->assertSee('Belanja Siklus Aktif')
        ->assertDontSee('Belanja Siklus Lalu')
        ->assertSee('Menampilkan Siklus Payday (P2P)');

    // Filter previous P2P cycle (-1)
    $this->actingAs($user)
        ->get('/transaksi?cycle=-1')
        ->assertStatus(200)
        ->assertSee('Belanja Siklus Lalu')
        ->assertDontSee('Belanja Siklus Aktif');
});

test('spending insights and dashboard integrate payday countdown and p2p pool', function () {
    $user = User::factory()->create(['payday_day' => 25]);
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 3_000_000]);

    $service = app(SpendingInsightService::class);
    $result = $service->build($user);

    expect($result['safe_to_spend']['payday_cycle']['is_custom'])->toBeTrue()
        ->and($result['safe_to_spend']['payday_cycle']['payday_day'])->toBe(25);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('menuju Payday (Tgl 25)');
});

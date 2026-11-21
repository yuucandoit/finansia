<?php

use App\Models\RecurringTransaction;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('recurring transactions page requires authentication', function () {
    $this->get('/transaksi-rutin')->assertRedirect('/login');
});

test('user can create execute and toggle recurring transaction', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'current_balance' => 2_000_000,
    ]);

    $this->actingAs($user)->post('/transaksi-rutin/store', [
        'description'   => 'Langganan Internet',
        'type'          => 'Cash Out',
        'amount'        => 350_000,
        'savings_id'    => $saving->id,
        'frequency'     => 'monthly',
        'next_run_date' => now()->toDateString(),
    ])->assertRedirect(route('recurring.index'));

    $rec = RecurringTransaction::where('user_id', $user->id)->first();
    expect($rec)->not->toBeNull();
    expect($rec->is_active)->toBeTrue();

    // Run manually
    $this->actingAs($user)->post("/transaksi-rutin/{$rec->id}/run")->assertRedirect(route('recurring.index'));

    expect((float) $saving->fresh()->current_balance)->toBe(1_650_000.0);
    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'savings_id'  => $saving->id,
        'description' => 'Langganan Internet (Rutin)',
        'amount'      => 350_000,
    ]);

    expect($rec->fresh()->next_run_date->format('Y-m-d'))->toBe(now()->addMonthNoOverflow()->format('Y-m-d'));

    // Toggle pause
    $this->actingAs($user)->patch("/transaksi-rutin/{$rec->id}/toggle")->assertRedirect();
    expect($rec->fresh()->is_active)->toBeFalse();
});

test('artisan finance process recurring command processes due schedules automatically', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'current_balance' => 1_000_000,
    ]);

    RecurringTransaction::create([
        'user_id'       => $user->id,
        'savings_id'    => $saving->id,
        'type'          => 'Cash In',
        'description'   => 'Gaji Mingguan',
        'amount'        => 1_500_000,
        'frequency'     => 'weekly',
        'next_run_date' => now()->toDateString(),
        'is_active'     => true,
    ]);

    $this->artisan('finance:process-recurring')->assertSuccessful();

    expect((float) $saving->fresh()->current_balance)->toBe(2_500_000.0);
    expect(Transaction::where('user_id', $user->id)->count())->toBe(1);
});

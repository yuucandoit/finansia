<?php

use App\Models\Debt;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('debt page requires authentication', function () {
    $this->get('/hutang-piutang')->assertRedirect('/login');
});

test('user can create payable debt and pay in installments with saving deduction', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'current_balance' => 5_000_000,
    ]);

    $this->actingAs($user)->post('/hutang-piutang/store', [
        'type'         => 'payable',
        'person_name'  => 'Budi Santoso',
        'description'  => 'Pinjaman Modal',
        'total_amount' => 1_000_000,
        'due_date'     => '2026-12-31',
    ])->assertRedirect(route('debt.index'));

    $debt = Debt::where('user_id', $user->id)->first();
    expect($debt)->not->toBeNull();
    expect($debt->status)->toBe('unpaid');

    // Pay first installment 400,000 -> partial
    $this->actingAs($user)->post("/hutang-piutang/{$debt->id}/pay", [
        'amount'     => 400_000,
        'savings_id' => $saving->id,
        'paid_at'    => '2026-11-19',
        'notes'      => 'Cicilan 1',
    ])->assertRedirect(route('debt.index'));

    $debt->refresh();
    expect($debt->status)->toBe('partial');
    expect((float) $debt->paid_amount)->toBe(400_000.0);
    expect((float) $saving->fresh()->current_balance)->toBe(4_600_000.0);

    // Pay remaining 600,000 -> paid
    $this->actingAs($user)->post("/hutang-piutang/{$debt->id}/pay", [
        'amount'     => 600_000,
        'savings_id' => $saving->id,
        'paid_at'    => '2026-11-20',
        'notes'      => 'Pelunasan',
    ])->assertRedirect(route('debt.index'));

    $debt->refresh();
    expect($debt->status)->toBe('paid');
    expect($debt->remaining_amount)->toBe(0.0);
    expect((float) $saving->fresh()->current_balance)->toBe(4_000_000.0);
});

test('receiving payment on receivable debt increments saving balance', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'current_balance' => 1_000_000,
    ]);

    $debt = Debt::create([
        'user_id'      => $user->id,
        'type'         => 'receivable',
        'person_name'  => 'Andi',
        'total_amount' => 500_000,
        'paid_amount'  => 0,
        'status'       => 'unpaid',
    ]);

    $this->actingAs($user)->post("/hutang-piutang/{$debt->id}/pay", [
        'amount'     => 500_000,
        'savings_id' => $saving->id,
        'paid_at'    => '2026-11-19',
    ])->assertRedirect();

    expect($debt->fresh()->status)->toBe('paid');
    expect((float) $saving->fresh()->current_balance)->toBe(1_500_000.0);
    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'savings_id'  => $saving->id,
        'type'        => 'Cash In',
        'direction'   => 'in',
        'amount'      => 500_000,
        'description' => 'Terima Piutang dari Andi',
    ]);
});

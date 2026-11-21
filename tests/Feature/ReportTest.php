<?php

use App\Models\Asset;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('report page requires authentication', function () {
    $this->get('/laporan')->assertRedirect('/login');
});

test('user can view monthly report export xlsx and print pdf view', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 3_000_000]);
    $cat = Category::create(['user_id' => $user->id, 'name' => 'Makanan', 'type' => 'Cash Out']);

    Budget::upsertBudget($user->id, 'Makanan', $cat->id, 11, 2026, 1_500_000);

    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash In',
        'direction'        => 'in',
        'description'      => 'Gaji November',
        'amount'           => 8_000_000,
        'transaction_date' => '2026-11-01',
    ]);

    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $cat->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Belanja Mingguan',
        'amount'           => 1_000_000,
        'transaction_date' => '2026-11-10',
    ]);

    Asset::create([
        'user_id'                => $user->id,
        'name'                   => 'Emas 2g',
        'asset_type'             => 'gold',
        'unit_label'             => 'gram',
        'quantity'               => 2,
        'buy_price_per_unit'     => 1_200_000,
        'current_price_per_unit' => 1_400_000,
    ]);

    $res = $this->actingAs($user)->get('/laporan?mode=monthly&month=11&year=2026');
    $res->assertStatus(200);
    $report = $res->viewData('report');
    expect($report['total_income'])->toBe(8_000_000.0);
    expect($report['total_expense'])->toBe(1_000_000.0);
    expect($report['net_cashflow'])->toBe(7_000_000.0);
    expect($report['net_worth_summary']['net_worth'])->toBe(5_800_000.0);

    // Export Excel (.xlsx)
    $xlsxRes = $this->actingAs($user)->get('/laporan/export-excel?mode=monthly&month=11&year=2026');
    $xlsxRes->assertStatus(200);
    $xlsxRes->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($xlsxRes->streamedContent())->not->toBeEmpty();

    // Print PDF View
    $pdfRes = $this->actingAs($user)->get('/laporan/print-pdf?mode=monthly&month=11&year=2026');
    $pdfRes->assertStatus(200)
        ->assertSee('FINANSIA')
        ->assertSee('Belanja Mingguan');
});

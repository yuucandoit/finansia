<?php

use App\Models\Asset;
use App\Models\Debt;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('asset page requires authentication', function () {
    $this->get('/aset')->assertRedirect('/login');
});

test('user can create update price and compute net worth with assets', function () {
    $user = User::factory()->create(['status' => 'approved']);
    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);
    Debt::create([
        'user_id'      => $user->id,
        'type'         => Debt::TYPE_PAYABLE,
        'person_name'  => 'Bank',
        'total_amount' => 2_000_000,
        'paid_amount'  => 500_000,
        'status'       => Debt::STATUS_PARTIAL,
    ]);

    // Store 10g Gold bought at 1,300,000/g, current price 1,450,000/g
    $this->actingAs($user)->post('/aset/store', [
        'name'                   => 'Emas Antam',
        'asset_type'             => 'gold',
        'unit_label'             => 'gram',
        'quantity'               => 10,
        'buy_price_per_unit'     => 1_300_000,
        'current_price_per_unit' => 1_450_000,
    ])->assertRedirect(route('asset.index'));

    $asset = Asset::where('user_id', $user->id)->first();
    expect($asset)->not->toBeNull();
    expect($asset->total_cost)->toBe(13_000_000.0);
    expect($asset->current_value)->toBe(14_500_000.0);
    expect($asset->unrealized_gain)->toBe(1_500_000.0);

    // Update current market price to 1,500,000/g
    $this->actingAs($user)->patch("/aset/{$asset->id}/price", [
        'current_price_per_unit' => 1_500_000,
    ])->assertRedirect(route('asset.index'));

    $res = $this->actingAs($user)->get('/aset');
    $res->assertStatus(200);
    // Net Worth = 5,000,000 (Savings) + 15,000,000 (Assets) - 1,500,000 (Debt) = 18,500,000
    expect((float) $res->viewData('netWorth'))->toBe(18_500_000.0);
});

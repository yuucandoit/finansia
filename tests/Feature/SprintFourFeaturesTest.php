<?php

use App\Models\FuturePlan;
use App\Models\FuturePlanActivity;
use App\Models\FuturePlanItem;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('simulation page requires authentication and renders baseline with future plans', function () {
    $this->get('/simulasi')->assertRedirect('/login');

    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 10_000_000]);

    $plan = FuturePlan::create([
        'user_id' => $user->id,
        'saving_id' => $saving->id,
        'title' => 'Dana Rumah',
        'status' => 'planned',
    ]);
    FuturePlanItem::create([
        'future_plan_id' => $plan->id,
        'title' => 'DP Rumah',
        'quantity' => 1,
        'unit_price' => 50_000_000,
    ]);

    $this->actingAs($user)
        ->get('/simulasi')
        ->assertStatus(200)
        ->assertSee('Simulasi Proyeksi Tabungan')
        ->assertSee('Dana Rumah');
});

test('future plan logs collaboration activities and displays them on show page', function () {
    $user = User::factory()->create(['name' => 'Raka']);
    $plan = FuturePlan::create([
        'user_id' => $user->id,
        'title' => 'Rencana Nikah',
        'status' => 'planned',
    ]);

    $this->actingAs($user)
        ->post(route('future-planner.items.store', $plan), [
            'title' => 'Catering Utama',
            'quantity' => 1,
            'unit_price' => 15_000_000,
        ])
        ->assertRedirect();

    $item = FuturePlanItem::where('future_plan_id', $plan->id)->first();
    expect($item)->not->toBeNull();

    $this->actingAs($user)
        ->patch(route('future-planner.items.toggle', [$plan, $item]))
        ->assertRedirect();

    $activities = FuturePlanActivity::where('future_plan_id', $plan->id)->get();
    expect($activities->pluck('action')->all())
        ->toContain('item_added')
        ->toContain('item_toggled');

    $this->actingAs($user)
        ->get(route('future-planner.show', $plan))
        ->assertStatus(200)
        ->assertSee('Riwayat Aktivitas Kolaborasi')
        ->assertSee('Catering Utama');
});

test('backup download requires authentication and returns isolated user json', function () {
    $this->get('/backup/download')->assertRedirect('/login');

    $user = User::factory()->create(['name' => 'Dina']);
    $otherUser = User::factory()->create(['name' => 'Rahasia']);

    Saving::factory()->create(['user_id' => $user->id, 'name' => 'Tabungan Dina', 'current_balance' => 5_000_000]);
    Saving::factory()->create(['user_id' => $otherUser->id, 'name' => 'Tabungan Orang Lain', 'current_balance' => 9_000_000]);

    $response = $this->actingAs($user)->get('/backup/download');

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/json; charset=utf-8');

    $json = $response->streamedContent();
    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()
        ->and($decoded['account']['email'])->toBe($user->email)
        ->and(collect($decoded['savings'])->pluck('name')->all())
        ->toContain('Tabungan Dina')
        ->not->toContain('Tabungan Orang Lain');
});

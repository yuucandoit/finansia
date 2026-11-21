<?php

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai chat generates future plan draft with gantt dates from prompt', function () {
    $user = User::factory()->create(['status' => 'approved']);
    config(['services.openrouter.api_key' => '']);

    $response = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'Buatkan rencana rakit PC budget 15jt selama 3 bulan',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('intent', 'RECORD_FUTURE_PLAN');

    $data = $response->json('data');
    expect($data['title'])->toContain('Rakit PC');
    expect($data['total_estimated'])->toEqual(15_000_000);
    expect(count($data['items']))->toBeGreaterThanOrEqual(3);
    expect($data['items'][0]['start_date'])->not->toBeNull();
    expect($data['items'][0]['end_date'])->not->toBeNull();
});

test('user can save ai generated future plan with items and gantt dates', function () {
    $user = User::factory()->create(['status' => 'approved']);

    $response = $this->actingAs($user)->postJson('/ai-chat/save-future-plan', [
        'title'       => 'Rencana Liburan Bali',
        'description' => 'Liburan akhir tahun',
        'target_date' => '2026-12-25',
        'items'       => [
            [
                'title'      => 'Tiket Pesawat',
                'quantity'   => 2,
                'unit_price' => 1_500_000,
                'start_date' => '2026-11-22',
                'end_date'   => '2026-11-30',
            ],
            [
                'title'      => 'Hotel 3 Malam',
                'quantity'   => 1,
                'unit_price' => 3_000_000,
                'start_date' => '2026-12-01',
                'end_date'   => '2026-12-10',
            ],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');

    $plan = FuturePlan::where('user_id', $user->id)->where('title', 'Rencana Liburan Bali')->first();
    expect($plan)->not->toBeNull();
    expect($plan->items()->count())->toBe(2);
    expect((float) $plan->total_estimated_amount)->toBe(6_000_000.0);

    $item = FuturePlanItem::where('future_plan_id', $plan->id)->where('title', 'Tiket Pesawat')->first();
    expect($item->start_date->format('Y-m-d'))->toBe('2026-11-22');
    expect($item->end_date->format('Y-m-d'))->toBe('2026-11-30');
});

test('pwa manifest and service worker files exist and layout links manifest', function () {
    expect(file_exists(public_path('manifest.json')))->toBeTrue();
    expect(file_exists(public_path('sw.js')))->toBeTrue();

    $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
    expect($manifest['short_name'])->toBe('Finansia');
    expect($manifest['display'])->toBe('standalone');

    $user = User::factory()->create(['status' => 'approved']);
    $this->actingAs($user)->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('manifest.json', false)
        ->assertSee('/sw.js', false);
});

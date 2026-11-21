<?php

use App\Models\Category;
use App\Models\Debt;
use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\RecurringTransaction;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('financial context snapshot includes health metrics future plans debts and recurring bills', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'name'            => 'BCA Utama',
        'current_balance' => 10_000_000,
    ]);

    $catFood = Category::create(['user_id' => $user->id, 'name' => 'Makanan & Minuman', 'type' => 'Cash Out']);

    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash In',
        'direction'        => 'in',
        'description'      => 'Gaji',
        'amount'           => 8_000_000,
        'transaction_date' => now()->toDateString(),
    ]);

    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $catFood->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Makan',
        'amount'           => 2_000_000,
        'transaction_date' => now()->toDateString(),
    ]);

    $plan = FuturePlan::create([
        'user_id'   => $user->id,
        'saving_id' => $saving->id,
        'title'     => 'Dana Darurat & Nikah',
        'status'    => FuturePlan::STATUS_IN_PROGRESS,
    ]);
    FuturePlanItem::create([
        'future_plan_id' => $plan->id,
        'title'          => 'Cincin',
        'quantity'       => 1,
        'unit_price'     => 5_000_000,
        'order'          => 1,
    ]);

    Debt::create([
        'user_id'      => $user->id,
        'type'         => Debt::TYPE_PAYABLE,
        'person_name'  => 'Budi',
        'total_amount' => 1_000_000,
        'paid_amount'  => 250_000,
        'status'       => Debt::STATUS_PARTIAL,
    ]);

    RecurringTransaction::create([
        'user_id'       => $user->id,
        'savings_id'    => $saving->id,
        'type'          => 'Cash Out',
        'description'   => 'Internet',
        'amount'        => 350_000,
        'frequency'     => 'monthly',
        'next_run_date' => now()->addDays(5)->toDateString(),
        'is_active'     => true,
    ]);

    $snapshot = app(FinancialContextService::class)->getUserSnapshot($user);

    expect($snapshot['kesehatan_finansial']['savings_rate_persen'])->toBe(75.0);
    expect($snapshot['kesehatan_finansial']['rasio_dana_darurat_bulan'])->toBe(5.0);
    expect($snapshot['rencana_masa_depan'])->toHaveCount(1);
    expect($snapshot['rencana_masa_depan'][0]['judul'])->toBe('Dana Darurat & Nikah');
    expect($snapshot['hutang_piutang']['total_sisa_hutang'])->toBe(750000);
    expect($snapshot['transaksi_rutin_aktif']['jumlah_jadwal'])->toBe(1);
    expect($snapshot['transaksi_rutin_aktif']['pengeluaran_rutin'])->toBe(350000);
});

test('ai chat parse handles financial advisor consultation and multi turn history', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create([
        'user_id'         => $user->id,
        'name'            => 'Mandiri',
        'current_balance' => 6_000_000,
    ]);

    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash In',
        'direction'        => 'in',
        'description'      => 'Gaji',
        'amount'           => 5_000_000,
        'transaction_date' => now()->toDateString(),
    ]);

    // Test with local fallback (when OpenRouter API key is empty or offline)
    config(['services.openrouter.api_key' => '']);

    $response = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'Cek kesehatan keuangan saya bulan ini dan kasih saran hematnya',
        'history' => [
            ['role' => 'user', 'content' => 'Halo Finansia'],
            ['role' => 'assistant', 'content' => 'Halo! Ada yang bisa saya bantu?'],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('intent', 'FINANCIAL_ADVISOR');

    expect($response->json('reply'))->toContain('Evaluasi Finansia Advisor');
    expect($response->json('reply'))->toContain('Rp 6.000.000');
});

test('openrouter service forwards conversation history to api during advisor chat', function () {
    $user = User::factory()->create(['status' => 'approved']);
    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 4_000_000]);

    config(['services.openrouter.api_key' => 'test-key-123']);

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::sequence()
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'intent' => 'FINANCIAL_ADVISOR',
                            'data'   => null,
                            'reply'  => 'Analisis...',
                        ]),
                    ],
                ]],
            ])
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => 'Halo! Berdasarkan saldo Rp 4.000.000, kondisi Anda cukup stabil.',
                    ],
                ]],
            ]),
    ]);

    $response = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'Kalau buat dana darurat gimana sarannya?',
        'history' => [
            ['role' => 'user', 'content' => 'Berapa saldo saya?'],
            ['role' => 'assistant', 'content' => 'Saldo Anda Rp 4.000.000.'],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('intent', 'FINANCIAL_ADVISOR')
        ->assertJsonPath('reply', 'Halo! Berdasarkan saldo Rp 4.000.000, kondisi Anda cukup stabil.');

    Http::assertSentCount(2);
});

test('ai chat answers general data query like top 5 pengeluaran bulan ini from db', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 10_000_000]);

    $expenses = [
        ['desc' => 'Sewa Apartemen', 'amount' => 3_500_000],
        ['desc' => 'Servis Mobil',   'amount' => 1_200_000],
        ['desc' => 'Belanja Bulanan','amount' => 850_000],
        ['desc' => 'Listrik PLN',    'amount' => 450_000],
        ['desc' => 'Internet WiFi',  'amount' => 350_000],
        ['desc' => 'Kopi Harian',    'amount' => 25_000],
    ];

    foreach ($expenses as $exp) {
        Transaction::create([
            'user_id'          => $user->id,
            'savings_id'       => $saving->id,
            'type'             => 'Cash Out',
            'direction'        => 'out',
            'description'      => $exp['desc'],
            'amount'           => $exp['amount'],
            'transaction_date' => now()->toDateString(),
        ]);
    }

    config(['services.openrouter.api_key' => '']);

    $response = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'sebutkan top 5 pengeluaran bulan ini',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('intent', 'FINANCIAL_QUERY');

    $reply = $response->json('reply');
    expect($reply)->toContain('Sewa Apartemen')
        ->toContain('3.500.000')
        ->toContain('Internet WiFi')
        ->not->toContain('Kopi Harian');
});

test('ai chat executes dynamic db query for arbitrary period limit and min amount', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create(['user_id' => $user->id, 'name' => 'BCA', 'current_balance' => 10_000_000]);

    $lastMonthDate = now()->subMonthNoOverflow()->startOfMonth()->addDays(5)->toDateString();

    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Tiket Pesawat Bulan Lalu',
        'amount'           => 2_400_000,
        'transaction_date' => $lastMonthDate,
    ]);
    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Hotel Bulan Lalu',
        'amount'           => 950_000,
        'transaction_date' => $lastMonthDate,
    ]);
    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'type'             => 'Cash Out',
        'direction'        => 'out',
        'description'      => 'Bakso Bulan Lalu',
        'amount'           => 30_000,
        'transaction_date' => $lastMonthDate,
    ]);

    config(['services.openrouter.api_key' => '']);

    $response = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'tampilkan top 2 pengeluaran di atas 500rb bulan lalu',
    ]);

    $response->assertStatus(200);
    $reply = $response->json('reply');

    expect($reply)->toContain('Tiket Pesawat Bulan Lalu')
        ->toContain('Hotel Bulan Lalu')
        ->not->toContain('Bakso Bulan Lalu');
});

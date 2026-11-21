<?php

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('credit card page requires authentication', function () {
    $this->get('/kartu-kredit')->assertRedirect('/login');
});

test('user can manage credit cards, installments, and pay installment deducting saving balance', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);

    // Create card
    $this->actingAs($user)
        ->post(route('credit-card.store'), [
            'name' => 'BCA Everyday',
            'last_four' => '8899',
            'credit_limit' => 10_000_000,
            'current_balance' => 1_000_000,
            'billing_day' => 5,
            'due_day' => 20,
        ])
        ->assertRedirect(route('credit-card.index'));

    $card = CreditCard::where('user_id', $user->id)->first();
    expect($card)->not->toBeNull();

    // Add 3-month installment of 3.000.000 (1.000.000 / month)
    $this->actingAs($user)
        ->post(route('credit-card.installments.store', $card), [
            'title' => 'Monitor 4K',
            'total_amount' => 3_000_000,
            'total_months' => 3,
            'paid_months' => 2,
        ])
        ->assertRedirect(route('credit-card.index'));

    $ins = CreditCardInstallment::where('credit_card_id', $card->id)->first();
    expect($ins)->not->toBeNull()
        ->and((float) $ins->monthly_amount)->toBe(1_000_000.0)
        ->and((float) $card->fresh('installments')->total_used)->toBe(2_000_000.0)
        ->and($card->fresh('installments')->utilization_percentage)->toBe(20.0);

    // Other user cannot pay or delete
    $this->actingAs($otherUser)
        ->post(route('credit-card.installments.pay', $ins))
        ->assertStatus(403);

    // Pay final (3rd) month from saving
    $this->actingAs($user)
        ->post(route('credit-card.installments.pay', $ins), [
            'savings_id' => $saving->id,
        ])
        ->assertRedirect(route('credit-card.index'));

    expect($ins->fresh()->paid_months)->toBe(3)
        ->and($ins->fresh()->status)->toBe('paid')
        ->and((float) $saving->fresh()->current_balance)->toBe(4_000_000.0);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'amount' => 1_000_000,
        'description' => 'Cicilan Monitor 4K (3/3)',
    ]);

    // Page renders card & KPI
    $this->actingAs($user)
        ->get(route('credit-card.index'))
        ->assertStatus(200)
        ->assertSee('BCA Everyday')
        ->assertSee('Monitor 4K');
});

test('backup preview and idempotent restore works cleanly from json file', function () {
    $user = User::factory()->create();

    $backupPayload = [
        'exported_at' => now()->toIso8601String(),
        'categories' => [
            ['id' => 99, 'name' => 'Hobi Restore', 'type' => 'Cash Out', 'color' => 'purple', 'icon' => 'fa-tag'],
        ],
        'savings' => [
            ['id' => 88, 'name' => 'Dompet Restore', 'current_balance' => 2500000],
        ],
        'transactions' => [
            [
                'savings_id' => 88,
                'category_id' => 99,
                'type' => 'Cash Out',
                'description' => 'Beli Buku Restore',
                'amount' => 125000,
                'transaction_date' => now()->toDateString(),
            ],
        ],
        'credit_cards' => [
            [
                'name' => 'Mandiri Platinum',
                'credit_limit' => 15000000,
                'current_balance' => 500000,
                'billing_day' => 1,
                'due_day' => 15,
                'installments' => [
                    [
                        'title' => 'HP Baru',
                        'total_amount' => 6000000,
                        'monthly_amount' => 1000000,
                        'total_months' => 6,
                        'paid_months' => 1,
                        'start_date' => now()->toDateString(),
                        'status' => 'active',
                    ],
                ],
            ],
        ],
    ];

    $file = UploadedFile::fake()->createWithContent(
        'finansia-backup.json',
        json_encode($backupPayload)
    );

    // 1. Dry-run preview
    $this->actingAs($user)
        ->post(route('profile.backup.preview'), ['backup_file' => $file])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('backup_restore_summary');

    // Preview does not mutate DB yet
    expect(Saving::where('user_id', $user->id)->count())->toBe(0);

    // 2. Execute restore
    $this->actingAs($user)
        ->post(route('profile.backup.restore'))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('backup_status');

    expect(Saving::where('user_id', $user->id)->where('name', 'Dompet Restore')->exists())->toBeTrue()
        ->and(Transaction::where('user_id', $user->id)->where('description', 'Beli Buku Restore')->exists())->toBeTrue()
        ->and(CreditCard::where('user_id', $user->id)->where('name', 'Mandiri Platinum')->exists())->toBeTrue()
        ->and(CreditCardInstallment::where('user_id', $user->id)->where('title', 'HP Baru')->exists())->toBeTrue();
});

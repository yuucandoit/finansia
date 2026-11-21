<?php

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use App\Models\Debt;
use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\RecurringTransaction;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notification feed requires authentication', function () {
    $this->get('/notifikasi/feed')->assertRedirect('/login');
});

test('notification feed aggregates budget, recurring, debt, and future plan alerts for authenticated user only', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $saving = Saving::factory()->create(['user_id' => $user->id]);
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Makan',
        'type' => 'expense',
    ]);

    // 1. Over-budget item
    $budget = Budget::create([
        'user_id' => $user->id,
        'month' => (int) now()->month,
        'year' => (int) now()->year,
    ]);
    BudgetItem::create([
        'budget_id' => $budget->id,
        'category_id' => $category->id,
        'name' => 'Jajan Kopi',
        'amount' => 100000,
    ]);
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $category->id,
        'type' => 'Cash Out',
        'amount' => 150000,
        'description' => 'Kopi susu sebulan',
        'transaction_date' => now()->toDateString(),
    ]);

    // 2. Due recurring transaction
    RecurringTransaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $category->id,
        'type' => 'Cash Out',
        'description' => 'Langganan Internet',
        'amount' => 350000,
        'frequency' => 'monthly',
        'next_run_date' => now()->toDateString(),
        'is_active' => true,
    ]);

    // 3. Overdue debt
    Debt::create([
        'user_id' => $user->id,
        'type' => Debt::TYPE_PAYABLE,
        'person_name' => 'Budi',
        'description' => 'Pinjaman darurat',
        'total_amount' => 500000,
        'paid_amount' => 0,
        'due_date' => now()->subDay()->toDateString(),
        'status' => Debt::STATUS_UNPAID,
    ]);

    // 4. Overdue future plan item
    $plan = FuturePlan::create([
        'user_id' => $user->id,
        'saving_id' => $saving->id,
        'title' => 'Rencana Liburan',
        'status' => 'active',
    ]);
    FuturePlanItem::create([
        'future_plan_id' => $plan->id,
        'title' => 'Beli Tiket Pesawat',
        'quantity' => 1,
        'unit_price' => 1200000,
        'is_completed' => false,
        'end_date' => now()->subDay()->toDateString(),
    ]);

    // Data milik user lain tidak boleh ikut muncul
    Debt::create([
        'user_id' => $otherUser->id,
        'type' => Debt::TYPE_PAYABLE,
        'person_name' => 'Rahasia Orang Lain',
        'total_amount' => 999000,
        'paid_amount' => 0,
        'due_date' => now()->subDay()->toDateString(),
        'status' => Debt::STATUS_UNPAID,
    ]);

    $response = $this->actingAs($user)->getJson('/notifikasi/feed');

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('count', 4);

    $items = collect($response->json('items'));
    expect($items->pluck('title')->implode(' | '))
        ->toContain('Over-Budget: Jajan Kopi')
        ->toContain('Langganan Internet')
        ->toContain('Budi')
        ->toContain('Beli Tiket Pesawat')
        ->not->toContain('Rahasia Orang Lain');
});

test('dashboard renders notification center and privacy mode toggle', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('openNotificationCenter', false)
        ->assertSee('togglePrivacyMode', false)
        ->assertSee('Sensor Nominal', false)
        ->assertSee('/notifikasi/feed', false);
});

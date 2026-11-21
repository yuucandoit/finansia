<?php

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SpendingInsightService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('spending insight service computes daily safe to spend, mom deltas, and heatmap accurately', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 10_000_000]);

    $catFood = Category::create(['user_id' => $user->id, 'name' => 'Makanan', 'type' => 'Cash Out']);
    $catTrans = Category::create(['user_id' => $user->id, 'name' => 'Transportasi', 'type' => 'Cash Out']);

    // Active month budget = Rp 3.000.000
    $budget = Budget::create([
        'user_id' => $user->id,
        'month' => (int) now()->month,
        'year' => (int) now()->year,
    ]);
    BudgetItem::create([
        'budget_id' => $budget->id,
        'category_id' => $catFood->id,
        'name' => 'Pos Makan',
        'amount' => 2_000_000,
    ]);
    BudgetItem::create([
        'budget_id' => $budget->id,
        'category_id' => $catTrans->id,
        'name' => 'Pos Transport',
        'amount' => 1_000_000,
    ]);

    // Last month transactions: Makanan 500k, Transportasi 600k
    $lastMonthDate = now()->subMonthNoOverflow()->startOfMonth()->addDays(5)->toDateString();
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $catFood->id,
        'type' => 'Cash Out',
        'amount' => 500_000,
        'description' => 'Makan bulan lalu',
        'transaction_date' => $lastMonthDate,
    ]);
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $catTrans->id,
        'type' => 'Cash Out',
        'amount' => 600_000,
        'description' => 'Transport bulan lalu',
        'transaction_date' => $lastMonthDate,
    ]);

    // This month transactions: Makanan 900k (up +80%), Transportasi 300k (down -50%)
    $todayStr = now()->toDateString();
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $catFood->id,
        'type' => 'Cash Out',
        'amount' => 900_000,
        'description' => 'Makan bulan ini',
        'transaction_date' => $todayStr,
    ]);
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $catTrans->id,
        'type' => 'Cash Out',
        'amount' => 300_000,
        'description' => 'Transport bulan ini',
        'transaction_date' => $todayStr,
    ]);

    $service = app(SpendingInsightService::class);
    $result = $service->build($user, $budget->fresh('items'));

    $daysRemaining = max(1, now()->daysInMonth - now()->day + 1);
    $expectedDailySafe = round((3_000_000 - 1_200_000) / $daysRemaining, 0);

    expect($result['safe_to_spend']['has_budget'])->toBeTrue()
        ->and($result['safe_to_spend']['remaining_pool'])->toBe(1_800_000.0)
        ->and($result['safe_to_spend']['daily_safe'])->toBe($expectedDailySafe)
        ->and($result['safe_to_spend']['spent_today'])->toBe(1_200_000.0)
        ->and($result['safe_to_spend']['status'])->toBe('safe')
        ->and($result['mom_insights']['top_spike']['category'])->toBe('Makanan')
        ->and($result['mom_insights']['top_spike']['delta_pct'])->toBe(80.0)
        ->and($result['mom_insights']['biggest_saving']['category'])->toBe('Transportasi')
        ->and($result['mom_insights']['biggest_saving']['delta_pct'])->toBe(-50.0)
        ->and($result['heatmap']['max_day']['day'])->toBe((int) now()->day)
        ->and($result['heatmap']['max_day']['amount'])->toBe(1_200_000.0)
        ->and($result['heatmap']['no_spend_days'])->toBe(max(0, now()->day - 1));
});

test('dashboard renders daily safe to spend, mom insights, and spending heatmap', function () {
    $user = User::factory()->create();
    Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 5_000_000]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('Daily Safe-to-Spend')
        ->assertSee('Spending Insights')
        ->assertSee('Kalender Pengeluaran')
        ->assertSee('No-Spend Day');
});

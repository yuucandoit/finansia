<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed default categories for testing
    Category::create([
        'name'  => 'Makanan & Minuman',
        'type'  => 'Cash Out',
        'color' => 'amber',
        'icon'  => 'fa-utensils',
    ]);
    Category::create([
        'name'  => 'Transportasi',
        'type'  => 'Cash Out',
        'color' => 'blue',
        'icon'  => 'fa-car',
    ]);
});

// ─────────────────────────────────────────────
//  WEB ROUTE TESTS
// ─────────────────────────────────────────────

test('guest cannot access budget page', function () {
    $this->get('/anggaran')->assertRedirect('/login');
});

test('authenticated user can view budget page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/anggaran')
        ->assertStatus(200)
        ->assertViewIs('budget')
        ->assertSee('Anggaran Seluruh Kebutuhan');
});

test('user can create a monthly budget for a category', function () {
    $user = User::factory()->create();
    $category = Category::where('name', 'Makanan & Minuman')->first();

    $response = $this->actingAs($user)->post('/anggaran/store', [
        'category_id' => $category->id,
        'amount'      => 1500000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response->assertRedirect('/anggaran?month=9&year=2026');
    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 1500000,
        'month'       => 9,
        'year'        => 2026,
    ]);
});

test('user can create a monthly budget with custom kebutuhan name via web', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/anggaran/store', [
        'new_kebutuhan' => 'Bayar Kos',
        'amount'        => 1750000,
        'month'         => 9,
        'year'          => 2026,
    ]);

    $response->assertRedirect('/anggaran?month=9&year=2026');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'Bayar Kos',
    ]);

    $category = Category::where('user_id', $user->id)->where('name', 'Bayar Kos')->first();

    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 1750000,
        'month'       => 9,
        'year'        => 2026,
    ]);
});

test('storing budget for existing category in same month updates the amount', function () {
    $user = User::factory()->create();
    $category = Category::where('name', 'Makanan & Minuman')->first();

    Budget::create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 1000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $this->actingAs($user)->post('/anggaran/store', [
        'category_id' => $category->id,
        'amount'      => 2000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 2000000,
        'month'       => 9,
        'year'        => 2026,
    ]);
    expect(Budget::where('user_id', $user->id)->count())->toBe(1);
});

test('user can update budget amount via web', function () {
    $user = User::factory()->create();
    $category = Category::where('name', 'Makanan & Minuman')->first();

    $budget = Budget::create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 1000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response = $this->actingAs($user)->put("/anggaran/{$budget->id}", [
        'amount' => 1750000,
    ]);

    $response->assertRedirect('/anggaran?month=9&year=2026');
    expect($budget->fresh()->amount)->toEqual(1750000);
});

test('user cannot update another user budget via web', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $category = Category::where('name', 'Makanan & Minuman')->first();

    $budget = Budget::create([
        'user_id'     => $userA->id,
        'category_id' => $category->id,
        'amount'      => 1000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $this->actingAs($userB)
        ->put("/anggaran/{$budget->id}", ['amount' => 5000000])
        ->assertStatus(403);
});

test('user can delete a budget via web', function () {
    $user = User::factory()->create();
    $category = Category::where('name', 'Makanan & Minuman')->first();

    $budget = Budget::create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 1000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response = $this->actingAs($user)->delete("/anggaran/{$budget->id}");
    $response->assertRedirect('/anggaran?month=9&year=2026');
    $this->assertSoftDeleted('budgets', ['id' => $budget->id]);
});

// ─────────────────────────────────────────────
//  REAL-TIME SPENDING CALCULATION TEST
// ─────────────────────────────────────────────

test('budget spent, remaining, percentage, and status reflect actual transactions', function () {
    $user = User::factory()->create();
    $category = Category::where('name', 'Makanan & Minuman')->first();

    $saving = Saving::create([
        'user_id'         => $user->id,
        'name'            => 'Dompet Tunai',
        'account_number'  => 'CASH',
        'current_balance' => 5000000,
    ]);

    $budget = Budget::create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'amount'      => 1000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    // Initial state: 0 spent
    expect($budget->spent)->toEqual(0.0);
    expect($budget->remaining)->toEqual(1000000.0);
    expect($budget->percentage)->toEqual(0.0);
    expect($budget->status)->toBe('safe');

    // Add transaction 1 (Rp 400.000)
    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $category->id,
        'type'             => 'Cash Out',
        'amount'           => 400000,
        'transaction_date' => Carbon::create(2026, 9, 10, 12, 0, 0),
        'description'      => 'Makan siang',
    ]);

    $freshBudget = $budget->fresh();
    expect($freshBudget->spent)->toEqual(400000.0);
    expect($freshBudget->remaining)->toEqual(600000.0);
    expect($freshBudget->percentage)->toEqual(40.0);
    expect($freshBudget->status)->toBe('safe');

    // Add transaction 2 (Rp 450.000) -> total Rp 850.000 (85% -> warning)
    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $category->id,
        'type'             => 'Cash Out',
        'amount'           => 450000,
        'transaction_date' => Carbon::create(2026, 9, 15, 19, 0, 0),
        'description'      => 'Dinner keluarga',
    ]);

    $freshBudget2 = $budget->fresh();
    expect($freshBudget2->spent)->toEqual(850000.0);
    expect($freshBudget2->remaining)->toEqual(150000.0);
    expect($freshBudget2->percentage)->toEqual(85.0);
    expect($freshBudget2->status)->toBe('warning');

    // Add transaction 3 (Rp 200.000) -> total Rp 1.050.000 (105% -> overbudget)
    Transaction::create([
        'user_id'          => $user->id,
        'savings_id'       => $saving->id,
        'category_id'      => $category->id,
        'type'             => 'Cash Out',
        'amount'           => 200000,
        'transaction_date' => Carbon::create(2026, 9, 20, 10, 0, 0),
        'description'      => 'Kopi & snack',
    ]);

    $freshBudget3 = $budget->fresh();
    expect($freshBudget3->spent)->toEqual(1050000.0);
    expect($freshBudget3->remaining)->toEqual(-50000.0);
    expect($freshBudget3->percentage)->toEqual(105.0);
    expect($freshBudget3->status)->toBe('overbudget');
});

// ─────────────────────────────────────────────
//  REST API TESTS
// ─────────────────────────────────────────────

test('api budgets requires sanctum authentication', function () {
    $this->getJson('/api/v1/budgets')->assertStatus(401);
});

test('api user can get budgets with summary and available categories', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cat1 = Category::where('name', 'Makanan & Minuman')->first();

    Budget::create([
        'user_id'     => $user->id,
        'category_id' => $cat1->id,
        'amount'      => 2000000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response = $this->getJson('/api/v1/budgets?month=9&year=2026');

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.period.month', 9)
        ->assertJsonPath('data.period.year', 2026)
        ->assertJsonPath('data.summary.total_budget', 2000000)
        ->assertJsonCount(1, 'data.budgets')
        ->assertJsonCount(1, 'data.available_categories'); // Transportasi is still unbudgeted
});

test('api user can store budget via POST /api/v1/budgets', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cat = Category::where('name', 'Transportasi')->first();

    $response = $this->postJson('/api/v1/budgets', [
        'category_id' => $cat->id,
        'amount'      => 500000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.amount', 500000)
        ->assertJsonPath('data.category.name', 'Transportasi');

    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'amount'      => 500000,
    ]);
});

test('api user can update budget via PUT /api/v1/budgets/{id}', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cat = Category::where('name', 'Transportasi')->first();
    $budget = Budget::create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'amount'      => 500000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response = $this->putJson("/api/v1/budgets/{$budget->id}", [
        'amount' => 750000,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.amount', 750000);

    expect($budget->fresh()->amount)->toEqual(750000);
});

test('api user can delete budget via DELETE /api/v1/budgets/{id}', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cat = Category::where('name', 'Transportasi')->first();
    $budget = Budget::create([
        'user_id'     => $user->id,
        'category_id' => $cat->id,
        'amount'      => 500000,
        'month'       => 9,
        'year'        => 2026,
    ]);

    $response = $this->deleteJson("/api/v1/budgets/{$budget->id}");

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');

    $this->assertSoftDeleted('budgets', ['id' => $budget->id]);
});

test('api user can store budget with custom kebutuhan name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/budgets', [
        'kebutuhan' => 'Skincare & Perawatan',
        'amount'    => 450000,
        'month'     => 9,
        'year'      => 2026,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.category.name', 'Skincare & Perawatan')
        ->assertJsonPath('data.amount', 450000);

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'Skincare & Perawatan',
    ]);
});

test('api user can batch store multiple budgets', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/budgets/batch', [
        'items' => [
            ['kebutuhan' => 'Kos Bulanan', 'amount' => 1500000, 'month' => 9, 'year' => 2026],
            ['kebutuhan' => 'Sedekah', 'amount' => 200000, 'month' => 9, 'year' => 2026],
            ['category'  => 'Makanan & Minuman', 'amount' => 1200000, 'month' => 9, 'year' => 2026],
        ],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.count', 3)
        ->assertJsonPath('data.total_amount', 2900000);

    expect(Budget::where('user_id', $user->id)->count())->toBe(3);
});

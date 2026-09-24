<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use App\Services\FinancialContextService;
use App\Services\OpenRouterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Category::create([
        'name'  => 'Makanan & Minuman',
        'type'  => 'Cash Out',
        'color' => 'amber',
        'icon'  => 'fa-utensils',
    ]);
    Category::create([
        'name'  => 'Belanja',
        'type'  => 'Cash Out',
        'color' => 'pink',
        'icon'  => 'fa-shopping-bag',
    ]);
    Category::create([
        'name'  => 'Transportasi',
        'type'  => 'Cash Out',
        'color' => 'blue',
        'icon'  => 'fa-car',
    ]);
});

// ─────────────────────────────────────────────
//  1. BUDGET SERVICE TESTS
// ─────────────────────────────────────────────

test('BudgetService records single budget correctly', function () {
    $user = User::factory()->create();
    $service = new BudgetService();

    $budget = $service->recordBudget($user, 'Makanan & Minuman', 1500000, 9, 2026);

    expect($budget)->toBeInstanceOf(Budget::class)
        ->and($budget->amount)->toEqual(1500000)
        ->and($budget->month)->toBe(9)
        ->and($budget->year)->toBe(2026);

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'amount'  => 1500000,
        'month'   => 9,
        'year'    => 2026,
    ]);
});

test('BudgetService records multiple budgets in one call', function () {
    $user = User::factory()->create();
    $service = new BudgetService();

    $items = [
        ['category' => 'Makanan & Minuman', 'amount' => 1200000, 'month' => 9, 'year' => 2026],
        ['category' => 'Belanja', 'amount' => 600000, 'month' => 9, 'year' => 2026],
        ['category' => 'Transportasi', 'amount' => 300000, 'month' => 9, 'year' => 2026],
    ];

    $result = $service->recordMultipleBudgets($user, $items);

    expect($result['success'])->toBeTrue()
        ->and($result['count'])->toBe(3)
        ->and($result['total_amount'])->toEqual(2100000);

    $this->assertDatabaseCount('budgets', 3);
});

// ─────────────────────────────────────────────
//  2. AI PARSER (LOCAL FALLBACK) TESTS
// ─────────────────────────────────────────────

test('AI parses single budget request correctly', function () {
    $service = new OpenRouterService();

    $parsed = $service->fallbackLocalParser('Atur anggaran makan 1.5jt bulan ini', ['BCA'], ['Makanan & Minuman', 'Belanja', 'Transportasi']);

    expect($parsed['intent'])->toBe('RECORD_BUDGET')
        ->and($parsed['data']['items'])->toHaveCount(1)
        ->and($parsed['data']['items'][0]['category'])->toBe('Makanan & Minuman')
        ->and($parsed['data']['items'][0]['amount'])->toBe(1500000);
});

test('AI parses multiple budgets in a single sentence', function () {
    $service = new OpenRouterService();

    $parsed = $service->fallbackLocalParser(
        'catat anggaran makan 1.5jt dan belanja 500rb',
        ['BCA'],
        ['Makanan & Minuman', 'Belanja', 'Transportasi']
    );

    expect($parsed['intent'])->toBe('RECORD_BUDGET')
        ->and($parsed['data']['items'])->toHaveCount(2)
        ->and($parsed['data']['items'][0]['category'])->toBe('Makanan & Minuman')
        ->and($parsed['data']['items'][0]['amount'])->toBe(1500000)
        ->and($parsed['data']['items'][1]['category'])->toBe('Belanja')
        ->and($parsed['data']['items'][1]['amount'])->toBe(500000);
});

test('AI parses custom kebutuhan from budget request', function () {
    $service = new OpenRouterService();

    $parsed = $service->fallbackLocalParser(
        'Atur anggaran bayar kos 1.5jt dan sedekah 200rb',
        ['BCA'],
        ['Makanan & Minuman', 'Belanja', 'Transportasi']
    );

    expect($parsed['intent'])->toBe('RECORD_BUDGET')
        ->and($parsed['data']['items'])->toHaveCount(2)
        ->and($parsed['data']['items'][0]['category'])->toBe('Bayar Kos')
        ->and($parsed['data']['items'][0]['amount'])->toBe(1500000)
        ->and($parsed['data']['items'][1]['category'])->toBe('Sedekah')
        ->and($parsed['data']['items'][1]['amount'])->toBe(200000);
});

test('AI parses multiple cashflow transactions in a single sentence', function () {
    $service = new OpenRouterService();

    $parsed = $service->fallbackLocalParser(
        'beli kopi 25k dan bensin 30k pake bca',
        ['BCA'],
        ['Makanan & Minuman', 'Transportasi']
    );

    expect($parsed['intent'])->toBe('RECORD_TRANSACTION')
        ->and($parsed['data']['items'])->toHaveCount(2)
        ->and($parsed['data']['items'][0]['amount'])->toBe(25000)
        ->and($parsed['data']['items'][1]['amount'])->toBe(30000);
});

// ─────────────────────────────────────────────
//  3. WEB CONTROLLER ENDPOINT TESTS
// ─────────────────────────────────────────────

test('web AI parse endpoint returns draft for multiple budgets', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'Atur anggaran makan 1.5jt dan belanja 500rb',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('intent', 'RECORD_BUDGET')
        ->assertJsonPath('is_multiple', true)
        ->assertJsonCount(2, 'data.items');
});

test('web AI save-budget saves multiple budgets successfully', function () {
    $user = User::factory()->create();
    $cat1 = Category::where('name', 'Makanan & Minuman')->first();
    $cat2 = Category::where('name', 'Belanja')->first();

    $response = $this->actingAs($user)->postJson('/ai-chat/save-budget', [
        'items' => [
            ['category_id' => $cat1->id, 'amount' => 1500000, 'month' => 9, 'year' => 2026],
            ['category_id' => $cat2->id, 'amount' => 500000, 'month' => 9, 'year' => 2026],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseHas('budgets', ['user_id' => $user->id, 'category_id' => $cat1->id, 'amount' => 1500000]);
    $this->assertDatabaseHas('budgets', ['user_id' => $user->id, 'category_id' => $cat2->id, 'amount' => 500000]);
});

test('web AI save endpoint can save multiple cashflow transactions', function () {
    $user = User::factory()->create();
    $saving = Saving::create([
        'user_id'         => $user->id,
        'name'            => 'BCA Utama',
        'account_number'  => '1234567890',
        'current_balance' => 5000000,
    ]);
    $cat1 = Category::where('name', 'Makanan & Minuman')->first();
    $cat2 = Category::where('name', 'Transportasi')->first();

    $response = $this->actingAs($user)->postJson('/ai-chat/save', [
        'items' => [
            [
                'savings_id'  => $saving->id,
                'category_id' => $cat1->id,
                'type'        => 'Cash Out',
                'amount'      => 25000,
                'description' => 'Beli Kopi',
            ],
            [
                'savings_id'  => $saving->id,
                'category_id' => $cat2->id,
                'type'        => 'Cash Out',
                'amount'      => 30000,
                'description' => 'Beli Bensin',
            ],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('count', 2);

    expect($saving->fresh()->current_balance)->toEqual(5000000 - 55000);
    $this->assertDatabaseCount('transactions', 2);
});

// ─────────────────────────────────────────────
//  4. API ENDPOINT TESTS (SANCTUM)
// ─────────────────────────────────────────────

test('API parse endpoint processes multiple budget prompt', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/ai/parse', [
        'message' => 'Atur anggaran makan 1.5jt dan belanja 500rb',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.intent', 'RECORD_BUDGET')
        ->assertJsonPath('data.is_multiple', true)
        ->assertJsonCount(2, 'data.items');
});

test('API save-budget endpoint saves multiple budgets', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $cat1 = Category::where('name', 'Makanan & Minuman')->first();
    $cat2 = Category::where('name', 'Transportasi')->first();

    $response = $this->postJson('/api/v1/ai/save-budget', [
        'items' => [
            ['category_id' => $cat1->id, 'amount' => 2000000, 'month' => 9, 'year' => 2026],
            ['category_id' => $cat2->id, 'amount' => 750000, 'month' => 9, 'year' => 2026],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.count', 2);

    $this->assertDatabaseHas('budgets', ['user_id' => $user->id, 'category_id' => $cat1->id, 'amount' => 2000000]);
    $this->assertDatabaseHas('budgets', ['user_id' => $user->id, 'category_id' => $cat2->id, 'amount' => 750000]);
});

test('API save endpoint saves multiple cashflow transactions with overdraft protection', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $saving = Saving::create([
        'user_id'         => $user->id,
        'name'            => 'Dompet Tunai',
        'account_number'  => 'CASH',
        'current_balance' => 100000,
    ]);
    $cat = Category::where('name', 'Makanan & Minuman')->first();

    // Success test
    $response = $this->postJson('/api/v1/ai/save', [
        'items' => [
            [
                'savings_id'  => $saving->id,
                'category_id' => $cat->id,
                'type'        => 'Cash Out',
                'amount'      => 30000,
                'description' => 'Makan Bakso',
            ],
            [
                'savings_id'  => $saving->id,
                'category_id' => $cat->id,
                'type'        => 'Cash Out',
                'amount'      => 20000,
                'description' => 'Es Teh Manis',
            ],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.saved_count', 2);

    expect($saving->fresh()->current_balance)->toEqual(50000);

    // Overdraft test (attempting to spend 60.000 when only 50.000 remains)
    $failResponse = $this->postJson('/api/v1/ai/save', [
        'items' => [
            [
                'savings_id'  => $saving->id,
                'category_id' => $cat->id,
                'type'        => 'Cash Out',
                'amount'      => 60000,
                'description' => 'Beli Kaos',
            ],
        ],
    ]);

    $failResponse->assertStatus(422);
});

test('web AI parse and save-budget automatically creates custom kebutuhan for user', function () {
    $user = User::factory()->create();

    $parseResponse = $this->actingAs($user)->postJson('/ai-chat/parse', [
        'message' => 'Atur anggaran bayar kos 1.5jt dan sedekah 200rb',
    ]);

    $parseResponse->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('intent', 'RECORD_BUDGET')
        ->assertJsonPath('data.items.0.category_name', 'Bayar Kos')
        ->assertJsonPath('data.items.1.category_name', 'Sedekah');

    $items = $parseResponse->json('data.items');

    $saveResponse = $this->actingAs($user)->postJson('/ai-chat/save-budget', [
        'items' => [
            [
                'category_id' => $items[0]['category_id'],
                'amount'      => $items[0]['amount'],
                'month'       => $items[0]['month'],
                'year'        => $items[0]['year'],
            ],
            [
                'category_id' => $items[1]['category_id'],
                'amount'      => $items[1]['amount'],
                'month'       => $items[1]['month'],
                'year'        => $items[1]['year'],
            ],
        ],
    ]);

    $saveResponse->assertStatus(200)
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'Bayar Kos',
    ]);
    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'Sedekah',
    ]);
    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'category_id' => $items[0]['category_id'],
        'amount'      => 1500000,
    ]);
    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'category_id' => $items[1]['category_id'],
        'amount'      => 200000,
    ]);
});

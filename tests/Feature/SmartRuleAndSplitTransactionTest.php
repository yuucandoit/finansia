<?php

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create, toggle, match, and delete smart rules', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Transportasi',
        'type' => 'Cash Out',
    ]);

    $this->actingAs($user)
        ->post('/kategori/rules', [
            'keyword' => 'gojek, grab, merchant-unik-abc',
            'category_id' => $category->id,
        ])
        ->assertRedirect('/kategori');

    $rule = CategoryRule::where('user_id', $user->id)->first();
    expect($rule)->not->toBeNull()
        ->and($rule->is_active)->toBeTrue();

    // Live match endpoint
    $this->actingAs($user)
        ->getJson('/kategori/rules/match?description=' . urlencode('Bayar merchant-unik-abc'))
        ->assertStatus(200)
        ->assertJsonPath('matched', true)
        ->assertJsonPath('source', 'rule')
        ->assertJsonPath('category_id', $category->id);

    // Other user cannot toggle
    $this->actingAs($otherUser)
        ->patch("/kategori/rules/{$rule->id}/toggle")
        ->assertStatus(403);

    // Toggle off -> match should no longer match rule
    $this->actingAs($user)
        ->patch("/kategori/rules/{$rule->id}/toggle")
        ->assertRedirect('/kategori');

    expect($rule->fresh()->is_active)->toBeFalse();

    $this->actingAs($user)
        ->getJson('/kategori/rules/match?description=' . urlencode('Bayar merchant-unik-abc'))
        ->assertStatus(200)
        ->assertJsonPath('rule_id', null)
        ->assertJsonPath('source', 'category_fuzzy');

    // Delete rule
    $this->actingAs($user)
        ->delete("/kategori/rules/{$rule->id}")
        ->assertRedirect('/kategori');

    expect(CategoryRule::find($rule->id))->toBeNull();
});

test('storing manual transaction without category auto-applies active smart rule', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 1_000_000]);
    $category = Category::create([
        'user_id' => $user->id,
        'name' => 'Kopi & Jajan',
        'type' => 'Cash Out',
    ]);

    CategoryRule::create([
        'user_id' => $user->id,
        'keyword' => 'kopi kenangan, starbucks',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post('/transaksi/store', [
            'description' => 'Beli Kopi Kenangan Mantan',
            'type' => 'Cash Out',
            'amount' => 28000,
            'savings_id' => $saving->id,
            'transaction_date' => now()->toDateString(),
        ])
        ->assertRedirect('/transaksi');

    $tx = Transaction::where('user_id', $user->id)->first();
    expect($tx)->not->toBeNull()
        ->and($tx->category_id)->toBe($category->id)
        ->and((float) $saving->fresh()->current_balance)->toBe(972_000.0);
});

test('split transaction stores items, updates budget spent accurately, and rejects mismatched sum', function () {
    $user = User::factory()->create();
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 2_000_000]);

    $catFood = Category::create(['user_id' => $user->id, 'name' => 'Makanan', 'type' => 'Cash Out']);
    $catHome = Category::create(['user_id' => $user->id, 'name' => 'Perlengkapan Rumah', 'type' => 'Cash Out']);

    $budget = Budget::create([
        'user_id' => $user->id,
        'month' => (int) now()->month,
        'year' => (int) now()->year,
    ]);
    $itemFood = BudgetItem::create([
        'budget_id' => $budget->id,
        'category_id' => $catFood->id,
        'name' => 'Pos Makanan',
        'amount' => 1_000_000,
    ]);
    $itemHome = BudgetItem::create([
        'budget_id' => $budget->id,
        'category_id' => $catHome->id,
        'name' => 'Pos Rumah',
        'amount' => 500_000,
    ]);

    // Mismatched split sum should fail
    $this->actingAs($user)
        ->post('/transaksi/store', [
            'description' => 'Belanja Superindo',
            'type' => 'Cash Out',
            'amount' => 500000,
            'savings_id' => $saving->id,
            'transaction_date' => now()->toDateString(),
            'splits' => [
                ['category_id' => $catFood->id, 'budget_item_id' => $itemFood->id, 'amount' => 300000],
                ['category_id' => $catHome->id, 'budget_item_id' => $itemHome->id, 'amount' => 100000],
            ],
        ])
        ->assertSessionHas('error');

    expect(Transaction::where('user_id', $user->id)->count())->toBe(0);

    // Valid split sum
    $this->actingAs($user)
        ->post('/transaksi/store', [
            'description' => 'Belanja Superindo',
            'type' => 'Cash Out',
            'amount' => 500000,
            'savings_id' => $saving->id,
            'transaction_date' => now()->toDateString(),
            'splits' => [
                ['category_id' => $catFood->id, 'budget_item_id' => $itemFood->id, 'amount' => 300000, 'description' => 'Beras & lauk'],
                ['category_id' => $catHome->id, 'budget_item_id' => $itemHome->id, 'amount' => 200000, 'description' => 'Sabun & tisu'],
            ],
        ])
        ->assertRedirect('/transaksi');

    $tx = Transaction::with('items')->where('user_id', $user->id)->first();
    expect($tx)->not->toBeNull()
        ->and($tx->items)->toHaveCount(2)
        ->and($tx->isSplit())->toBeTrue()
        ->and((float) $saving->fresh()->current_balance)->toBe(1_500_000.0)
        ->and($itemFood->fresh()->spent)->toBe(300_000.0)
        ->and($itemHome->fresh()->spent)->toBe(200_000.0);

    // Transaction page renders split badge
    $this->actingAs($user)
        ->get('/transaksi')
        ->assertStatus(200)
        ->assertSee('Split (2 pos)')
        ->assertSee('Aktifkan Split');
});

<?php

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('shared wallet allows owner to invite editor and viewer with proper permissions', function () {
    $owner = User::factory()->create(['name' => 'Bima']);
    $editor = User::factory()->create(['name' => 'Citra', 'email' => 'citra@example.com']);
    $viewer = User::factory()->create(['name' => 'Dodi', 'email' => 'dodi@example.com']);

    $saving = Saving::factory()->create([
        'user_id' => $owner->id,
        'name' => 'Tabungan Rumah Tangga',
        'current_balance' => 3_000_000,
    ]);

    // Invite editor & viewer
    $this->actingAs($owner)
        ->post(route('shared-wallet.members.store', $saving), [
            'email' => 'citra@example.com',
            'role' => 'editor',
        ])
        ->assertRedirect(route('shared-wallet.index'));

    $this->actingAs($owner)
        ->post(route('shared-wallet.members.store', $saving), [
            'email' => 'dodi@example.com',
            'role' => 'viewer',
        ])
        ->assertRedirect(route('shared-wallet.index'));

    // Editor sees shared wallet & records expense
    $this->actingAs($editor)
        ->get(route('shared-wallet.index'))
        ->assertStatus(200)
        ->assertSee('Tabungan Rumah Tangga');

    $this->actingAs($editor)
        ->post(route('shared-wallet.transactions.store', $saving), [
            'type' => 'Cash Out',
            'description' => 'Belanja Mingguan',
            'amount' => 450_000,
        ])
        ->assertRedirect(route('shared-wallet.index'));

    expect((float) $saving->fresh()->current_balance)->toBe(2_550_000.0);
    $this->assertDatabaseHas('transactions', [
        'savings_id' => $saving->id,
        'amount' => 450_000,
        'description' => 'Belanja Mingguan (oleh Citra)',
    ]);

    // Viewer cannot record transaction
    $this->actingAs($viewer)
        ->post(route('shared-wallet.transactions.store', $saving), [
            'type' => 'Cash Out',
            'description' => 'Coba Catat',
            'amount' => 10_000,
        ])
        ->assertStatus(403);

    // Owner removes editor
    $this->actingAs($owner)
        ->delete(route('shared-wallet.members.destroy', [$saving, $editor]))
        ->assertRedirect(route('shared-wallet.index'));

    expect($saving->fresh('members')->members->pluck('id')->all())->not->toContain($editor->id);
});

test('dashboard shows onboarding checklist for new user and hides it once 3 steps are completed', function () {
    $user = User::factory()->create();

    // New user sees 0/3 checklist + PIN overlay markup
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertSee('Panduan Mulai Cepat (0/3 Selesai)')
        ->assertSee('pin-lock-overlay', false);

    // Complete all 3 steps
    $saving = Saving::factory()->create(['user_id' => $user->id, 'current_balance' => 1_000_000]);
    $cat = Category::create(['user_id' => $user->id, 'name' => 'Makan', 'type' => 'Cash Out']);
    $budget = Budget::create(['user_id' => $user->id, 'month' => (int) now()->month, 'year' => (int) now()->year]);
    BudgetItem::create(['budget_id' => $budget->id, 'category_id' => $cat->id, 'name' => 'Pos Makan', 'amount' => 500_000]);
    Transaction::create([
        'user_id' => $user->id,
        'savings_id' => $saving->id,
        'category_id' => $cat->id,
        'type' => 'Cash Out',
        'amount' => 25_000,
        'description' => 'Nasi Goreng',
        'transaction_date' => now()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertDontSee('Siapkan Finansia dalam 3 Langkah Mudah');
});

test('profile page renders quick pin lock settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertStatus(200)
        ->assertSee('Kunci PIN Cepat')
        ->assertSee('saveQuickPin', false);
});

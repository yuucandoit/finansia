<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category page requires authentication', function () {
    $this->get('/kategori')->assertRedirect('/login');
});

test('user can view system categories and their own custom categories only', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $otherUser = User::factory()->create(['status' => 'approved']);

    Category::create(['user_id' => null, 'name' => 'Makanan Sistem', 'type' => 'Cash Out', 'color' => 'blue', 'icon' => 'fa-utensils']);
    Category::create(['user_id' => $user->id, 'name' => 'Hobi Rakit PC', 'type' => 'Cash Out', 'color' => 'indigo', 'icon' => 'fa-tag']);
    Category::create(['user_id' => $otherUser->id, 'name' => 'Rahasia Orang Lain', 'type' => 'Cash Out', 'color' => 'red', 'icon' => 'fa-tag']);

    $response = $this->actingAs($user)->get('/kategori');

    $response->assertStatus(200);
    $response->assertSee('Makanan Sistem');
    $response->assertSee('Hobi Rakit PC');
    $response->assertDontSee('Rahasia Orang Lain');
});

test('user can create update and delete custom category', function () {
    $user = User::factory()->create(['status' => 'approved']);

    // Create
    $this->actingAs($user)->post('/kategori/store', [
        'name'  => 'Kopi & Nongkrong',
        'type'  => 'Cash Out',
        'color' => 'amber',
        'icon'  => 'fa-utensils',
    ])->assertRedirect(route('category.index'));

    $cat = Category::where('user_id', $user->id)->where('name', 'Kopi & Nongkrong')->first();
    expect($cat)->not->toBeNull();

    // Update
    $this->actingAs($user)->put("/kategori/{$cat->id}", [
        'name'  => 'Ngopi Cantik',
        'type'  => 'Cash Out',
        'color' => 'pink',
        'icon'  => 'fa-utensils',
    ])->assertRedirect(route('category.index'));

    expect($cat->fresh()->name)->toBe('Ngopi Cantik');

    // Delete (SoftDelete)
    $this->actingAs($user)->delete("/kategori/{$cat->id}")->assertRedirect(route('category.index'));
    expect($cat->fresh()->trashed())->toBeTrue();
});

test('user cannot modify or delete system category or another users category', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $otherUser = User::factory()->create(['status' => 'approved']);

    $sysCat = Category::create(['user_id' => null, 'name' => 'Gaji Sistem', 'type' => 'Cash In']);
    $otherCat = Category::create(['user_id' => $otherUser->id, 'name' => 'Kategori Lain', 'type' => 'Cash Out']);

    $this->actingAs($user)->put("/kategori/{$sysCat->id}", ['name' => 'Hacked', 'type' => 'Cash In'])->assertStatus(403);
    $this->actingAs($user)->delete("/kategori/{$sysCat->id}")->assertStatus(403);
    $this->actingAs($user)->put("/kategori/{$otherCat->id}", ['name' => 'Hacked', 'type' => 'Cash Out'])->assertStatus(403);
    $this->actingAs($user)->delete("/kategori/{$otherCat->id}")->assertStatus(403);
});

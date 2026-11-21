<?php

use App\Models\Category;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('ai ocr endpoint requires authentication', function () {
    $this->postJson('/ai-chat/parse-image')->assertStatus(401);
});

test('ai ocr detects valid receipt image and returns confirmation transaction draft', function () {
    $user = User::factory()->create(['status' => 'approved']);
    $saving = Saving::factory()->create(['user_id' => $user->id, 'name' => 'BCA Utama', 'current_balance' => 1_000_000]);
    Category::create(['user_id' => $user->id, 'name' => 'Belanja', 'type' => 'Cash Out']);

    config(['services.openrouter.api_key' => 'test-key-ocr']);

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'is_valid'             => true,
                        'image_category'       => 'receipt',
                        'image_category_label' => 'Foto Struk',
                        'items'                => [[
                            'type'         => 'cash_out',
                            'amount'       => 87500,
                            'account_name' => 'BCA Utama',
                            'category'     => 'Belanja',
                            'description'  => 'Indomaret Mart',
                            'date'         => '2026-11-19',
                        ]],
                        'reply' => 'Terdeteksi Foto Struk Indomaret Mart sebesar Rp 87.500.',
                    ]),
                ],
            ]],
        ], 200),
    ]);

    $image = UploadedFile::fake()->image('struk.jpg', 400, 600);

    $response = $this->actingAs($user)->post('/ai-chat/parse-image', [
        'image' => $image,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('intent', 'RECORD_TRANSACTION')
        ->assertJsonPath('image_category', 'receipt')
        ->assertJsonPath('image_category_label', 'Foto Struk')
        ->assertJsonPath('data.items.0.amount', 87500)
        ->assertJsonPath('data.items.0.savings_id', $saving->id)
        ->assertJsonPath('data.items.0.type', 'Cash Out');
});

test('ai ocr detects valid payslip image as cash in transaction', function () {
    $user = User::factory()->create(['status' => 'approved']);
    Saving::factory()->create(['user_id' => $user->id, 'name' => 'Mandiri', 'current_balance' => 500_000]);

    config(['services.openrouter.api_key' => 'test-key-ocr']);

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'is_valid'             => true,
                        'image_category'       => 'payslip',
                        'image_category_label' => 'Foto Slip Gaji',
                        'items'                => [[
                            'type'         => 'cash_in',
                            'amount'       => 7500000,
                            'account_name' => 'Mandiri',
                            'category'     => 'Gaji',
                            'description'  => 'Slip Gaji November 2026',
                            'date'         => '2026-11-19',
                        ]],
                        'reply' => 'Terdeteksi Foto Slip Gaji sebesar Rp 7.500.000.',
                    ]),
                ],
            ]],
        ], 200),
    ]);

    $image = UploadedFile::fake()->image('slip_gaji.png', 500, 500);

    $response = $this->actingAs($user)->post('/ai-chat/parse-image', [
        'image' => $image,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('intent', 'RECORD_TRANSACTION')
        ->assertJsonPath('image_category', 'payslip')
        ->assertJsonPath('data.items.0.type', 'Cash In')
        ->assertJsonPath('data.items.0.amount', 7500000);
});

test('ai ocr rejects non financial image outside valid categories', function () {
    $user = User::factory()->create(['status' => 'approved']);
    Saving::factory()->create(['user_id' => $user->id, 'name' => 'BCA']);

    config(['services.openrouter.api_key' => 'test-key-ocr']);

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'is_valid'             => false,
                        'image_category'       => 'other',
                        'image_category_label' => 'Bukan Dokumen Keuangan',
                        'items'                => [],
                        'reply'                => 'Maaf, AI tidak dapat memproses gambar ini karena bukan termasuk kategori Screenshot M-Banking, Foto Struk, atau Foto Slip Gaji yang mengandung nominal transaksi.',
                    ]),
                ],
            ]],
        ], 200),
    ]);

    $image = UploadedFile::fake()->image('kucing.jpg', 300, 300);

    $response = $this->actingAs($user)->post('/ai-chat/parse-image', [
        'image' => $image,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('intent', 'INVALID_IMAGE')
        ->assertJsonPath('data', null);

    expect($response->json('reply'))->toContain('bukan termasuk kategori');
});

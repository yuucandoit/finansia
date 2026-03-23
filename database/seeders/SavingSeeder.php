<?php

namespace Database\Seeders;

use App\Models\Saving;
use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SavingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil bank-bank utama
        $bankBRI = Bank::where('name', 'LIKE', '%BRI%')->where('code', '002')->first();
        $bankMandiri = Bank::where('name', 'LIKE', '%Mandiri%')->where('code', '008')->first();
        $bankJago = Bank::where('name', 'LIKE', '%Jago%')->where('code', '472')->first();

        $savings = [
            [
                'user_id' => 1,
                'bank_id' => $bankBRI?->id,
                'name' => 'Tabungan Utama BRI',
                'account_number' => '0001-01-' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT) . '-30-3',
                'current_balance' => 5000000,
            ],
            [
                'user_id' => 1,
                'bank_id' => $bankMandiri?->id,
                'name' => 'Tabungan Mandiri',
                'account_number' => '144-00-' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT) . '-8',
                'current_balance' => 3500000,
            ],
            [
                'user_id' => 1,
                'bank_id' => $bankJago?->id,
                'name' => 'Jago Kantong Utama',
                'account_number' => '100' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'current_balance' => 2500000,
            ]
        ];

        foreach ($savings as $saving) {
            // Skip if bank not found
            if ($saving['bank_id']) {
                Saving::create($saving);
            }
        }

        $this->command->info('✅ Created ' . count($savings) . ' savings accounts successfully!');
    }
}

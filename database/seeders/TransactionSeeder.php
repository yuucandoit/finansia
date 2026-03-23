<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Transaction_Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 30 transaksi dengan items
        Transaction::factory()
            ->count(30)
            ->create()
            ->each(function ($transaction) {
                // Untuk transaksi Cash Out, buat 2-6 items
                if ($transaction->type === 'Cash Out') {
                    $itemCount = rand(2, 6);
                    
                    Transaction_Item::factory()
                        ->count($itemCount)
                        ->create([
                            'transaction_id' => $transaction->id,
                            'tr_date' => $transaction->transaction_date,
                        ]);
                } else {
                    // Untuk transaksi lain, buat 1-3 items
                    $itemCount = rand(1, 3);
                    
                    Transaction_Item::factory()
                        ->count($itemCount)
                        ->create([
                            'transaction_id' => $transaction->id,
                            'tr_date' => $transaction->transaction_date,
                        ]);
                }
            });

        $this->command->info('✅ Created 30 transactions with items successfully!');
    }
}

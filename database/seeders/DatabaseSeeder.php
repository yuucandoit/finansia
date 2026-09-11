<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Wahyu sanjaya',
            'email' => 'wahyusnjy@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        // Seed dalam urutan yang benar untuk foreign key dependencies
        $this->call([
            BankSeeder::class,        // Bank harus pertama
            CategorySeeder::class,    // Kategori
            SavingSeeder::class,      // Savings setelah bank
            // TransactionSeeder::class, // Transaction terakhir
        ]);
    }
}

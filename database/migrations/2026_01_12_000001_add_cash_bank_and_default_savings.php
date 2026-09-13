<?php

use App\Models\Bank;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan bank Tunai / Cash ada
        $cashBank = Bank::firstOrCreate(
            ['code' => '000'],
            ['name' => 'Tunai / Cash']
        );

        // 2. Buatkan default Dompet Tunai (Cash) untuk semua user yang ada
        $users = User::all();
        foreach ($users as $user) {
            Saving::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'account_number' => 'CASH',
                ],
                [
                    'bank_id' => $cashBank->id,
                    'name' => 'Dompet Tunai (Cash)',
                    'current_balance' => 0,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu menghapus tabungan pengguna yang sudah ada
    }
};

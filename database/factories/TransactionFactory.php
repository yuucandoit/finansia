<?php

namespace Database\Factories;

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $types = ['Cash In', 'Cash Out', 'Transfer', 'Tabungan'];
        $type  = $this->faker->randomElement($types);

        $descriptions = [
            'Cash In'  => ['Gaji Bulanan', 'Bonus Kinerja', 'Uang Saku', 'Freelance Project', 'Hasil Investasi', 'Pendapatan Passive'],
            'Cash Out' => ['Belanja Bulanan', 'Bayar Listrik', 'Bayar Internet', 'Makan di Restoran', 'Beli Keperluan', 'Bensin Motor', 'Subscription Netflix', 'Bayar Gym'],
            'Transfer' => ['Transfer ke Tabungan', 'Transfer ke Keluarga', 'Bayar Utang Teman', 'Bayar Kartu Kredit'],
            'Tabungan' => ['Tabungan Darurat', 'Tabungan Liburan', 'Tabungan Gadget Baru', 'Tabungan Pendidikan'],
        ];

        $description = $this->faker->randomElement($descriptions[$type]);

        $amount = match ($type) {
            'Cash In'  => $this->faker->numberBetween(1_000_000, 10_000_000),
            'Cash Out' => $this->faker->numberBetween(10_000, 500_000),
            'Transfer' => $this->faker->numberBetween(100_000, 2_000_000),
            'Tabungan' => $this->faker->numberBetween(50_000, 1_000_000),
        };

        return [
            'user_id'          => User::factory(),
            'savings_id'       => $this->faker->optional(0.7)->passthrough(null),
            'category_id'      => null,
            'type'             => $type,
            'description'      => $description,
            'amount'           => $amount,
            'fee'              => null,
            'is_manual'        => $this->faker->boolean(70),
            'transaction_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'attachment'       => null,
        ];
    }
}

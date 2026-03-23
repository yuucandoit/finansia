<?php

namespace Database\Factories;

use App\Models\Bank;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Saving>
 */
class SavingFactory extends Factory
{
    protected $model = Saving::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'bank_id'         => Bank::factory(),
            'name'            => $this->faker->randomElement([
                'Tabungan Utama', 'Tabungan Darurat', 'Tabungan Liburan', 'Dana Pensiun',
            ]),
            'account_number'  => $this->faker->numerify('##########'),
            'current_balance' => $this->faker->numberBetween(500_000, 50_000_000),
        ];
    }
}

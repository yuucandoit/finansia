<?php

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        static $seq = 0;
        $seq++;
        $banks = [
            ['code' => 'BCA',     'name' => 'Bank BCA'],
            ['code' => 'MANDIRI', 'name' => 'Bank Mandiri'],
            ['code' => 'BNI',     'name' => 'Bank BNI'],
            ['code' => 'BRI',     'name' => 'Bank BRI'],
            ['code' => 'BSI',     'name' => 'Bank BSI'],
        ];
        $pick = $banks[($seq - 1) % count($banks)];

        return [
            'code' => $pick['code'] . '_' . $seq,
            'name' => $pick['name'],
            'logo' => null,
        ];
    }
}

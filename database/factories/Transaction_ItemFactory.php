<?php

namespace Database\Factories;

use App\Models\Transaction_Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction_Item>
 */
class Transaction_ItemFactory extends Factory
{
    protected $model = Transaction_Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $items = [
            ['name' => 'Beras', 'unit' => 'kg', 'price_range' => [12000, 15000]],
            ['name' => 'Minyak Goreng', 'unit' => 'liter', 'price_range' => [15000, 20000]],
            ['name' => 'Gula Pasir', 'unit' => 'kg', 'price_range' => [12000, 14000]],
            ['name' => 'Telur', 'unit' => 'kg', 'price_range' => [25000, 30000]],
            ['name' => 'Ayam', 'unit' => 'kg', 'price_range' => [35000, 45000]],
            ['name' => 'Daging Sapi', 'unit' => 'kg', 'price_range' => [120000, 150000]],
            ['name' => 'Sabun Mandi', 'unit' => 'pcs', 'price_range' => [5000, 15000]],
            ['name' => 'Shampo', 'unit' => 'botol', 'price_range' => [15000, 35000]],
            ['name' => 'Pasta Gigi', 'unit' => 'pcs', 'price_range' => [8000, 12000]],
            ['name' => 'Deterjen', 'unit' => 'kg', 'price_range' => [20000, 30000]],
            ['name' => 'Kopi', 'unit' => 'pack', 'price_range' => [15000, 25000]],
            ['name' => 'Teh', 'unit' => 'pack', 'price_range' => [10000, 15000]],
            ['name' => 'Susu', 'unit' => 'kotak', 'price_range' => [15000, 25000]],
            ['name' => 'Roti', 'unit' => 'pack', 'price_range' => [10000, 20000]],
            ['name' => 'Buah-buahan', 'unit' => 'kg', 'price_range' => [15000, 40000]],
        ];
        
        $selectedItem = $this->faker->randomElement($items);
        $quantity = $this->faker->numberBetween(1, 10);
        $pricePerUnit = $this->faker->numberBetween($selectedItem['price_range'][0], $selectedItem['price_range'][1]);
        $total = $quantity * $pricePerUnit;
        
        // Add random discount or tax (0-10%)
        $adjustment = $this->faker->randomFloat(2, 0, 0.1);
        $grandTotal = $total * (1 + $adjustment);

        return [
            'transaction_id' => 1, // Will be set by seeder
            'name' => $selectedItem['name'],
            'quantity' => $quantity,
            'unit' => $selectedItem['unit'],
            'total' => $total,
            'grand_total' => $grandTotal,
            'tr_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

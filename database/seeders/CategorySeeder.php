<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Cash Out Categories
            [
                'name' => 'Makanan & Minuman',
                'type' => 'Cash Out',
                'icon' => 'fa-utensils',
                'color' => 'amber',
            ],
            [
                'name' => 'Belanja',
                'type' => 'Cash Out',
                'icon' => 'fa-shopping-bag',
                'color' => 'pink',
            ],
            [
                'name' => 'Transportasi',
                'type' => 'Cash Out',
                'icon' => 'fa-car',
                'color' => 'blue',
            ],
            [
                'name' => 'Utilitas & Tagihan',
                'type' => 'Cash Out',
                'icon' => 'fa-bolt',
                'color' => 'yellow',
            ],
            [
                'name' => 'Hiburan',
                'type' => 'Cash Out',
                'icon' => 'fa-gamepad',
                'color' => 'purple',
            ],
            [
                'name' => 'Kesehatan',
                'type' => 'Cash Out',
                'icon' => 'fa-heartbeat',
                'color' => 'red',
            ],
            [
                'name' => 'Tempat Tinggal',
                'type' => 'Cash Out',
                'icon' => 'fa-house',
                'color' => 'indigo',
            ],
            [
                'name' => 'Keluarga & Sedekah',
                'type' => 'Cash Out',
                'icon' => 'fa-hand-holding-heart',
                'color' => 'emerald',
            ],
            [
                'name' => 'Tabungan & Investasi',
                'type' => 'Cash Out',
                'icon' => 'fa-piggy-bank',
                'color' => 'teal',
            ],
            [
                'name' => 'Pendidikan',
                'type' => 'Cash Out',
                'icon' => 'fa-graduation-cap',
                'color' => 'blue',
            ],
            [
                'name' => 'Lainnya',
                'type' => 'Cash Out',
                'icon' => 'fa-ellipsis-h',
                'color' => 'gray',
            ],

            // Cash In Categories
            [
                'name' => 'Gaji',
                'type' => 'Cash In',
                'icon' => 'fa-money-bill-wave',
                'color' => 'emerald',
            ],
            [
                'name' => 'Investasi',
                'type' => 'Cash In',
                'icon' => 'fa-chart-line',
                'color' => 'indigo',
            ],
            [
                'name' => 'Freelance / Project',
                'type' => 'Cash In',
                'icon' => 'fa-laptop-code',
                'color' => 'teal',
            ],
            [
                'name' => 'Bonus & Hadiah',
                'type' => 'Cash In',
                'icon' => 'fa-gift',
                'color' => 'orange',
            ],
            [
                'name' => 'Lainnya',
                'type' => 'Cash In',
                'icon' => 'fa-ellipsis-h',
                'color' => 'gray',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

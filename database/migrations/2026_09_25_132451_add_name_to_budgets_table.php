<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom name
        Schema::table('budgets', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->after('user_id');
        });

        // 2. Isi nilai awal name dari nama kategori jika ada data lama
        $budgets = DB::table('budgets')->get();
        foreach ($budgets as $budget) {
            $cat = DB::table('categories')->where('id', $budget->category_id)->first();
            $defaultName = $cat ? $cat->name : 'Anggaran #' . $budget->id;
            DB::table('budgets')->where('id', $budget->id)->update(['name' => $defaultName]);
        }

        // 3. Ubah unique constraint: sebelumnya per kategori, sekarang per nama pos anggaran
        Schema::table('budgets', function (Blueprint $table) {
            $table->string('name', 100)->nullable(false)->change();
            $table->dropUnique(['user_id', 'category_id', 'month', 'year']);
            $table->unique(['user_id', 'name', 'month', 'year']);
        });

        // 4. Pastikan kategori master baku tersedia
        $essentialCategories = [
            [
                'name'  => 'Tempat Tinggal',
                'type'  => 'Cash Out',
                'icon'  => 'fa-house',
                'color' => 'indigo',
            ],
            [
                'name'  => 'Keluarga & Sedekah',
                'type'  => 'Cash Out',
                'icon'  => 'fa-hand-holding-heart',
                'color' => 'emerald',
            ],
            [
                'name'  => 'Tabungan & Investasi',
                'type'  => 'Cash Out',
                'icon'  => 'fa-piggy-bank',
                'color' => 'teal',
            ],
            [
                'name'  => 'Pendidikan',
                'type'  => 'Cash Out',
                'icon'  => 'fa-graduation-cap',
                'color' => 'blue',
            ],
        ];

        foreach ($essentialCategories as $cat) {
            $exists = DB::table('categories')
                ->whereNull('user_id')
                ->where('name', $cat['name'])
                ->where('type', $cat['type'])
                ->exists();

            if (!$exists) {
                DB::table('categories')->insert(array_merge($cat, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'name', 'month', 'year']);
            $table->unique(['user_id', 'category_id', 'month', 'year']);
            $table->dropColumn('name');
        });
    }
};

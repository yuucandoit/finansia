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
        // 1. Create budget_items table
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('budget_id');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100);
            $table->decimal('amount', 16, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Add budget_item_id to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('budget_item_id')->nullable()->after('category_id')->constrained('budget_items')->nullOnDelete();
        });

        // 3. Migrate existing data from flat budgets to budget container + budget_items
        $existingBudgets = DB::table('budgets')->orderBy('id')->get();
        $grouped = $existingBudgets->groupBy(function ($item) {
            return $item->user_id . '_' . $item->month . '_' . $item->year;
        });

        $preservedBudgetIds = [];

        foreach ($grouped as $group) {
            $container = $group->first();
            $preservedBudgetIds[] = $container->id;

            foreach ($group as $item) {
                $itemId = DB::table('budget_items')->insertGetId([
                    'budget_id'   => $container->id,
                    'category_id' => $item->category_id ?? null,
                    'name'        => $item->name ?? 'Anggaran #' . $item->id,
                    'amount'      => $item->amount ?? 0,
                    'created_at'  => $item->created_at ?? now(),
                    'updated_at'  => $item->updated_at ?? now(),
                    'deleted_at'  => $item->deleted_at ?? null,
                ]);

                // Try linking existing transactions to this budget item
                if (!empty($item->category_id)) {
                    $query = DB::table('transactions')
                        ->where('user_id', $item->user_id)
                        ->where('category_id', $item->category_id)
                        ->whereNull('budget_item_id')
                        ->whereYear('transaction_date', $item->year)
                        ->whereMonth('transaction_date', $item->month);

                    if (!empty($item->name) && $group->where('category_id', $item->category_id)->count() > 1) {
                        $nameLower = strtolower(trim($item->name));
                        $query->whereRaw('LOWER(description) LIKE ?', ['%' . $nameLower . '%']);
                    }

                    $query->update(['budget_item_id' => $itemId]);
                }
            }
        }

        // Delete old redundant budget header rows
        if (!empty($preservedBudgetIds)) {
            DB::table('budgets')->whereNotIn('id', $preservedBudgetIds)->delete();
        }

        // 4. Alter budgets table: drop old constraints & columns, add unique(user_id, month, year)
        Schema::table('budgets', function (Blueprint $table) {
            try {
                $table->dropForeign(['category_id']);
            } catch (\Throwable $e) {}

            try {
                $table->dropUnique(['user_id', 'name', 'month', 'year']);
            } catch (\Throwable $e) {}

            $table->dropColumn(['name', 'category_id', 'amount']);
            $table->unique(['user_id', 'month', 'year']);
        });

        // 5. Connect budget_items to budgets with foreign key and unique constraint
        Schema::table('budget_items', function (Blueprint $table) {
            $table->foreign('budget_id')->references('id')->on('budgets')->cascadeOnDelete();
            $table->unique(['budget_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('budget_item_id');
        });

        Schema::dropIfExists('budget_items');

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'month', 'year']);
            $table->string('name', 100)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->decimal('amount', 16, 2)->default(0);
            $table->unique(['user_id', 'name', 'month', 'year']);
        });
    }
};

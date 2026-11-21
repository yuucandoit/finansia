<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\CategoryRule;
use Carbon\Carbon;

class SmartRuleService
{
    public function __construct(
        protected BudgetService $budgetService
    ) {}

    /**
     * Cocokkan deskripsi transaksi dengan Smart Rules milik user.
     */
    public function matchDescription(int $userId, string $description, ?string $transactionDate = null): ?array
    {
        $rules = CategoryRule::with('category:id,name')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        foreach ($rules as $rule) {
            if (!$rule->matches($description)) {
                continue;
            }

            $budgetItemId = $rule->budget_item_id;
            if (!$budgetItemId && $rule->category_id) {
                $budgetItemId = $this->resolveActiveBudgetItemId($userId, $rule->category_id, $transactionDate);
            }

            return [
                'rule_id'        => $rule->id,
                'category_id'    => $rule->category_id,
                'category_name'  => $rule->category?->name,
                'budget_item_id' => $budgetItemId,
            ];
        }

        return null;
    }

    /**
     * Cari saran kategori & pos anggaran (Smart Rules -> fallback fuzzy category).
     */
    public function suggestForUser(int $userId, string $description, ?string $transactionDate = null): array
    {
        $ruleMatch = $this->matchDescription($userId, $description, $transactionDate);
        if ($ruleMatch) {
            return [
                'matched'        => true,
                'source'         => 'rule',
                'rule_id'        => $ruleMatch['rule_id'],
                'category_id'    => $ruleMatch['category_id'],
                'category_name'  => $ruleMatch['category_name'],
                'budget_item_id' => $ruleMatch['budget_item_id'],
            ];
        }

        $category = $this->budgetService->resolveCategory($description, $userId);
        if ($category) {
            return [
                'matched'        => true,
                'source'         => 'category_fuzzy',
                'rule_id'        => null,
                'category_id'    => $category->id,
                'category_name'  => $category->name,
                'budget_item_id' => $this->resolveActiveBudgetItemId($userId, $category->id, $transactionDate),
            ];
        }

        return [
            'matched'        => false,
            'source'         => null,
            'rule_id'        => null,
            'category_id'    => null,
            'category_name'  => null,
            'budget_item_id' => null,
        ];
    }

    protected function resolveActiveBudgetItemId(int $userId, int $categoryId, ?string $transactionDate = null): ?int
    {
        $date = $transactionDate ? Carbon::parse($transactionDate) : now();
        $budget = Budget::where('user_id', $userId)
            ->where('month', (int) $date->month)
            ->where('year', (int) $date->year)
            ->first();

        if (!$budget) {
            return null;
        }

        $items = BudgetItem::where('budget_id', $budget->id)
            ->where('category_id', $categoryId)
            ->get();

        return $items->count() === 1 ? $items->first()->id : null;
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'budgets';

    protected $fillable = [
        'user_id',
        'month',
        'year',
    ];

    protected $casts = [
        'month'  => 'integer',
        'year'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class, 'budget_id');
    }

    /**
     * Dapatkan atau buat container budget bulanan untuk user, bulan, dan tahun tertentu.
     */
    public static function getOrCreateBudget(int $userId, int $month, int $year): self
    {
        $budget = static::withTrashed()
            ->where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($budget) {
            if ($budget->trashed()) {
                $budget->restore();
            }
            return $budget;
        }

        return static::create([
            'user_id' => $userId,
            'month'   => $month,
            'year'    => $year,
        ]);
    }

    /**
     * Menyimpan, memperbarui, atau merestore anggaran bulanan pengguna per pos kebutuhan (name).
     * Mengembalikan instance BudgetItem yang telah tersimpan.
     */
    public static function upsertBudget(
        int $userId,
        string|int $name,
        int|float|null $categoryId,
        ?int $month = null,
        ?int $year = null,
        ?float $amount = null
    ): BudgetItem {
        // Handle 5 arguments: upsertBudget($userId, $categoryId, $month, $year, $amount)
        if ($amount === null && is_numeric($name) && $year !== null) {
            $catId = (int) $name;
            $m = (int) $categoryId;
            $y = (int) $month;
            $amt = (float) $year;

            $cat = Category::find($catId);
            $trimmedName = $cat ? $cat->name : 'Anggaran';
            $categoryId = $catId;
            $month = $m;
            $year = $y;
            $amount = $amt;
        } else {
            $trimmedName = trim((string) $name);
            $categoryId = $categoryId !== null ? (int) $categoryId : null;
            $amount = (float) $amount;
        }

        $budget = static::getOrCreateBudget($userId, (int) $month, (int) $year);

        return BudgetItem::upsertItem($budget, $trimmedName, $categoryId, $amount);
    }

    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->items->sum('amount');
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->items->sum(fn($item) => $item->spent);
    }

    public function getTotalRemainingAttribute(): float
    {
        return (float) ($this->total_budget - $this->total_spent);
    }

    public function getOverallPercentageAttribute(): float
    {
        if ($this->total_budget <= 0) {
            return 0;
        }

        return round(($this->total_spent / $this->total_budget) * 100, 1);
    }

    public function getOverallStatusAttribute(): string
    {
        if ($this->total_spent > $this->total_budget && $this->total_budget > 0) {
            return 'overbudget';
        }

        if ($this->overall_percentage >= 80) {
            return 'warning';
        }

        return 'safe';
    }
}

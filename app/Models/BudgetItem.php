<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'budget_items';

    protected $fillable = [
        'budget_id',
        'category_id',
        'name',
        'amount',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'budget_id' => 'integer',
        'category_id' => 'integer',
    ];

    protected $appends = [
        'spent',
        'remaining',
        'percentage',
        'status',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'budget_item_id');
    }

    public function getMonthAttribute(): ?int
    {
        $budget = $this->relationLoaded('budget') ? $this->budget : ($this->budget_id ? Budget::find($this->budget_id) : null);
        return $budget ? (int) $budget->month : null;
    }

    public function getYearAttribute(): ?int
    {
        $budget = $this->relationLoaded('budget') ? $this->budget : ($this->budget_id ? Budget::find($this->budget_id) : null);
        return $budget ? (int) $budget->year : null;
    }

    public function getUserIdAttribute(): ?int
    {
        $budget = $this->relationLoaded('budget') ? $this->budget : ($this->budget_id ? Budget::find($this->budget_id) : null);
        return $budget ? (int) $budget->user_id : null;
    }

    /**
     * Hitung total pengeluaran riil pada pos kebutuhan ini.
     * Mengutamakan transaksi yang terhubung via budget_item_id,
     * dengan fallback cerdas untuk transaksi tanpa budget_item_id di kategori yang sama.
     */
    public function getSpentAttribute(): float
    {
        $budget = $this->relationLoaded('budget') ? $this->budget : ($this->budget_id ? Budget::find($this->budget_id) : null);
        if (!$budget) {
            return (float) $this->transactions()->whereIn('type', ['Cash Out'])->sum('amount');
        }

        $userId = $budget->user_id;
        $month = $budget->month;
        $year = $budget->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // 1. Transaksi yang secara eksplisit dikaitkan ke pos ini
        $directSpent = (float) Transaction::where('budget_item_id', $this->id)
            ->whereIn('type', ['Cash Out'])
            ->sum('amount');

        // 2. Transaksi unassigned (budget_item_id IS NULL) di kategori & rentang bulan yang sama
        $unassignedQuery = Transaction::where('user_id', $userId)
            ->whereNull('budget_item_id')
            ->where('category_id', $this->category_id)
            ->whereIn('type', ['Cash Out'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        // Cek apakah ada pos anggaran lain di kategori yang sama pada bulan ini
        $siblingCount = static::where('budget_id', $this->budget_id)
            ->where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->count();

        if ($siblingCount > 0 && !empty($this->name)) {
            $keywords = array_filter(explode(' ', strtolower(trim($this->name))), fn($w) => strlen($w) > 2);
            if (!empty($keywords)) {
                $unassignedQuery->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhereRaw('LOWER(description) LIKE ?', ['%' . $kw . '%']);
                    }
                });
            } else {
                $unassignedQuery->whereRaw('1 = 0');
            }
        }

        $unassignedSpent = (float) $unassignedQuery->sum('amount');

        return $directSpent + $unassignedSpent;
    }

    /**
     * Hitung sisa batas anggaran pos.
     */
    public function getRemainingAttribute(): float
    {
        return (float) ($this->amount - $this->spent);
    }

    /**
     * Persentase penggunaan anggaran pos.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->spent / $this->amount) * 100, 1);
    }

    /**
     * Status pos: 'safe', 'warning', 'overbudget'.
     */
    public function getStatusAttribute(): string
    {
        if ($this->spent > $this->amount) {
            return 'overbudget';
        }

        if ($this->percentage >= 80) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Upsert pos anggaran di dalam budget container.
     */
    public static function upsertItem(Budget $budget, string $name, ?int $categoryId, float $amount): self
    {
        $trimmedName = trim($name);

        $item = static::withTrashed()
            ->where('budget_id', $budget->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($trimmedName)])
            ->first();

        if ($item) {
            if ($item->trashed()) {
                $item->restore();
            }
            $item->name = $trimmedName;
            $item->category_id = $categoryId;
            $item->amount = $amount;
            $item->save();

            return $item;
        }

        return static::create([
            'budget_id'   => $budget->id,
            'name'        => $trimmedName,
            'category_id' => $categoryId,
            'amount'      => $amount,
        ]);
    }
}

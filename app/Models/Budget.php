<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'budgets';

    protected $fillable = [
        'user_id',
        'name',
        'category_id',
        'amount',
        'month',
        'year',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'month'  => 'integer',
        'year'   => 'integer',
    ];

    protected $appends = [
        'spent',
        'remaining',
        'percentage',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($budget) {
            if (empty($budget->name)) {
                $category = Category::find($budget->category_id);
                $budget->name = $category ? $category->name : 'Anggaran';
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Menyimpan, memperbarui, atau merestore anggaran bulanan pengguna per pos kebutuhan (name).
     * Mendukung pemanggilan 6 argumen (userId, name, categoryId, month, year, amount)
     * atau 5 argumen (userId, categoryId, month, year, amount) untuk kompatibilitas.
     * Mengatasi kendala unique constraint SQLSTATE[23505] pada PostgreSQL
     * ketika data anggaran sebelumnya berada dalam status soft-deleted.
     */
    public static function upsertBudget(
        int $userId,
        string|int $name,
        int|float $categoryId,
        ?int $month = null,
        ?int $year = null,
        ?float $amount = null
    ): self {
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
            $categoryId = (int) $categoryId;
            $amount = (float) $amount;
        }

        $budget = static::withTrashed()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(name) = ?', [strtolower($trimmedName)])
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($budget) {
            if ($budget->trashed()) {
                $budget->restore();
            }
            $budget->name = $trimmedName;
            $budget->category_id = $categoryId;
            $budget->amount = $amount;
            $budget->save();

            return $budget;
        }

        return static::create([
            'user_id'     => $userId,
            'name'        => $trimmedName,
            'category_id' => $categoryId,
            'month'       => $month,
            'year'        => $year,
            'amount'      => $amount,
        ]);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Hitung total pengeluaran riil pada pos anggaran & kategori ini.
     */
    public function getSpentAttribute(): float
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        // Cek apakah ada multiple pos anggaran di kategori yang sama pada bulan ini
        $otherBudgetsCount = static::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->where('id', '!=', $this->id)
            ->count();

        $query = Transaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->whereIn('type', ['Cash Out'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($otherBudgetsCount > 0 && !empty($this->name)) {
            // Filter cerdas berdasarkan kata kunci nama pos kebutuhan
            $keywords = array_filter(explode(' ', strtolower(trim($this->name))), fn($w) => strlen($w) > 2);
            if (!empty($keywords)) {
                $query->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhereRaw('LOWER(description) LIKE ?', ['%' . $kw . '%']);
                    }
                });
            }
        }

        return (float) $query->sum('amount');
    }

    /**
     * Hitung sisa anggaran.
     */
    public function getRemainingAttribute(): float
    {
        return (float) ($this->amount - $this->spent);
    }

    /**
     * Persentase anggaran yang telah terpakai.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->spent / $this->amount) * 100, 1);
    }

    /**
     * Status anggaran: 'safe' (< 80%), 'warning' (80% - 100%), 'overbudget' (> 100%).
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
}

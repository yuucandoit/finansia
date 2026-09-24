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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Hitung total pengeluaran riil pada kategori & periode anggaran ini.
     */
    public function getSpentAttribute(): float
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        return (float) Transaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->whereIn('type', ['Cash Out'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
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

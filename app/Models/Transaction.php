<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'savings_id',
        'category_id',
        'budget_item_id',
        'type',
        'direction',
        'transfer_group_id',
        'description',
        'amount',
        'fee',
        'is_manual',
        'transaction_date',
        'attachment',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'fee'              => 'decimal:2',
        'is_manual'        => 'boolean',
        'transaction_date' => 'date',
        'budget_item_id'   => 'integer',
        'category_id'      => 'integer',
        'savings_id'       => 'integer',
        'user_id'          => 'integer',
    ];

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    // Kelompok arus uang untuk laporan/dashboard
    public const FLOW_INCOME = 'income';
    public const FLOW_EXPENSE = 'expense';
    public const FLOW_SAVINGS = 'savings';
    public const FLOW_INTERNAL = 'internal_transfer';

    protected static function booted(): void
    {
        static::creating(function (Transaction $tx) {
            if (empty($tx->direction)) {
                $tx->direction = $tx->type === 'Cash In' ? self::DIRECTION_IN : self::DIRECTION_OUT;
            }
        });
    }

    /**
     * Perubahan saldo rekening akibat transaksi ini (sudah termasuk fee).
     */
    public function balanceEffect(): float
    {
        $amount = (float) $this->amount;
        $fee = (float) ($this->fee ?? 0);

        return $this->direction === self::DIRECTION_IN ? $amount : -($amount + $fee);
    }

    /**
     * Klasifikasi arus uang:
     * - Transfer/Tabungan yang berpasangan (antar rekening sendiri) -> internal, tidak dihitung pemasukan/pengeluaran.
     * - Tabungan tanpa pasangan -> alokasi tabungan.
     * - Uang masuk lainnya -> pemasukan; uang keluar lainnya -> pengeluaran.
     */
    public function flowGroup(): string
    {
        if ($this->transfer_group_id) {
            return self::FLOW_INTERNAL;
        }

        if ($this->direction === self::DIRECTION_IN) {
            return self::FLOW_INCOME;
        }

        return $this->type === 'Tabungan' ? self::FLOW_SAVINGS : self::FLOW_EXPENSE;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function savingAccount(): BelongsTo
    {
        return $this->belongsTo(Saving::class, 'savings_id');
    }

    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }
}

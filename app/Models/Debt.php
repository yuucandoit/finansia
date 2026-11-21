<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use HasFactory;

    protected $table = 'debts';

    protected $fillable = [
        'user_id',
        'type',
        'person_name',
        'description',
        'total_amount',
        'paid_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_date'     => 'date:Y-m-d',
    ];

    public const TYPE_PAYABLE = 'payable';       // Hutang Saya ke orang/lembaga
    public const TYPE_RECEIVABLE = 'receivable'; // Piutang (orang lain berhutang ke saya)

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'debt_id')
            ->orderBy('paid_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getProgressPercentageAttribute(): float
    {
        $total = (float) $this->total_amount;
        if ($total <= 0) {
            return 100.0;
        }

        return min(100.0, round(((float) $this->paid_amount / $total) * 100, 1));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== self::STATUS_PAID
            && $this->due_date !== null
            && $this->due_date->lt(now()->startOfDay());
    }
}

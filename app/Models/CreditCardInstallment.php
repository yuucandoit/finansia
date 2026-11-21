<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardInstallment extends Model
{
    protected $fillable = [
        'credit_card_id',
        'user_id',
        'title',
        'total_amount',
        'monthly_amount',
        'total_months',
        'paid_months',
        'start_date',
        'status',
    ];

    protected $casts = [
        'credit_card_id' => 'integer',
        'user_id'        => 'integer',
        'total_amount'   => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'total_months'   => 'integer',
        'paid_months'    => 'integer',
        'start_date'     => 'date',
    ];

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class, 'credit_card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingMonthsAttribute(): int
    {
        return max(0, $this->total_months - $this->paid_months);
    }

    public function getRemainingAmountAttribute(): float
    {
        return round($this->remaining_months * (float) $this->monthly_amount, 2);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_months <= 0) {
            return 0.0;
        }

        return round(($this->paid_months / $this->total_months) * 100, 1);
    }
}

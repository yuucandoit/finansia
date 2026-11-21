<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditCard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'provider',
        'last_four',
        'credit_limit',
        'current_balance',
        'billing_day',
        'due_day',
        'is_active',
    ];

    protected $casts = [
        'user_id'         => 'integer',
        'credit_limit'    => 'decimal:2',
        'current_balance' => 'decimal:2',
        'billing_day'     => 'integer',
        'due_day'         => 'integer',
        'is_active'       => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(CreditCardInstallment::class, 'credit_card_id');
    }

    public function getActiveInstallmentsRemainingAttribute(): float
    {
        $items = $this->relationLoaded('installments')
            ? $this->installments
            : $this->installments()->get();

        return (float) $items
            ->where('status', 'active')
            ->sum(fn(CreditCardInstallment $ins) => $ins->remaining_amount);
    }

    public function getTotalUsedAttribute(): float
    {
        return (float) $this->current_balance + $this->active_installments_remaining;
    }

    public function getAvailableLimitAttribute(): float
    {
        return max(0.0, (float) $this->credit_limit - $this->total_used);
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ((float) $this->credit_limit <= 0) {
            return 0.0;
        }

        return round(($this->total_used / (float) $this->credit_limit) * 100, 1);
    }

    public function getMonthlyInstallmentBillAttribute(): float
    {
        $items = $this->relationLoaded('installments')
            ? $this->installments
            : $this->installments()->get();

        return (float) $items
            ->where('status', 'active')
            ->sum('monthly_amount');
    }

    public function getEstimatedStatementAttribute(): float
    {
        return (float) $this->current_balance + $this->monthly_installment_bill;
    }
}

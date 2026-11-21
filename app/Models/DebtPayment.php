<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtPayment extends Model
{
    use HasFactory;

    protected $table = 'debt_payments';

    protected $fillable = [
        'debt_id',
        'user_id',
        'savings_id',
        'transaction_id',
        'amount',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'debt_id'        => 'integer',
        'user_id'        => 'integer',
        'savings_id'     => 'integer',
        'transaction_id' => 'integer',
        'amount'         => 'decimal:2',
        'paid_at'        => 'date:Y-m-d',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class, 'debt_id');
    }

    public function savingAccount(): BelongsTo
    {
        return $this->belongsTo(Saving::class, 'savings_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}

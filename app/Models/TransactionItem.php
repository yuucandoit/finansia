<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $table = 'transaction_splits';

    protected $fillable = [
        'transaction_id',
        'category_id',
        'budget_item_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'category_id'    => 'integer',
        'budget_item_id' => 'integer',
        'amount'         => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }
}

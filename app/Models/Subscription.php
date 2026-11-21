<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_tier',
        'billing_cycle',
        'amount',
        'status',
        'order_id',
        'payment_provider',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'user_id'    => 'integer',
        'amount'     => 'decimal:2',
        'paid_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

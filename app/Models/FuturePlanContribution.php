<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuturePlanContribution extends Model
{
    use HasFactory;

    protected $table = 'future_plan_contributions';

    protected $fillable = [
        'future_plan_id',
        'user_id',
        'amount',
        'contributed_at',
        'notes',
    ];

    protected $casts = [
        'future_plan_id' => 'integer',
        'user_id'        => 'integer',
        'amount'         => 'decimal:2',
        'contributed_at' => 'date:Y-m-d',
    ];

    public function futurePlan(): BelongsTo
    {
        return $this->belongsTo(FuturePlan::class, 'future_plan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuturePlanActivity extends Model
{
    protected $fillable = [
        'future_plan_id',
        'user_id',
        'action',
        'description',
        'meta',
    ];

    protected $casts = [
        'future_plan_id' => 'integer',
        'user_id'        => 'integer',
        'meta'           => 'array',
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

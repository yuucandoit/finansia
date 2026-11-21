<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'user_id',
        'name',
        'asset_type',
        'unit_label',
        'quantity',
        'buy_price_per_unit',
        'current_price_per_unit',
        'notes',
        'last_valued_at',
    ];

    protected $casts = [
        'user_id'                => 'integer',
        'quantity'               => 'decimal:4',
        'buy_price_per_unit'     => 'decimal:2',
        'current_price_per_unit' => 'decimal:2',
        'last_valued_at'         => 'datetime',
    ];

    public const TYPES = [
        'gold'        => 'Emas / Logam Mulia',
        'mutual_fund' => 'Reksadana',
        'stock'       => 'Saham',
        'forex'       => 'Valas / Mata Uang Asing',
        'crypto'      => 'Aset Kripto',
        'property'    => 'Properti / Tanah',
        'other'       => 'Aset Lainnya',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->asset_type] ?? 'Aset Lainnya';
    }

    public function getTotalCostAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->buy_price_per_unit, 2);
    }

    public function getCurrentValueAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->current_price_per_unit, 2);
    }

    public function getUnrealizedGainAttribute(): float
    {
        return round($this->current_value - $this->total_cost, 2);
    }

    public function getUnrealizedGainPercentageAttribute(): float
    {
        $cost = $this->total_cost;
        if ($cost <= 0) {
            return 0.0;
        }

        return round(($this->unrealized_gain / $cost) * 100, 2);
    }
}

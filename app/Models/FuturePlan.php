<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuturePlan extends Model
{
    use HasFactory;

    protected $table = 'future_plans';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_date',
        'status',
        'color',
    ];

    protected $casts = [
        'target_date' => 'date',
        'user_id'     => 'integer',
    ];

    public const STATUS_PLANNED     = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FuturePlanItem::class, 'future_plan_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(FuturePlanItem::class, 'future_plan_id')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Jumlah keseluruhan item.
     */
    public function getTotalItemsCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items->count();
        }

        return $this->items()->count();
    }

    /**
     * Jumlah item yang sudah dicentang (selesai / terbeli).
     */
    public function getCompletedItemsCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items->where('is_completed', true)->count();
        }

        return $this->items()->where('is_completed', true)->count();
    }

    /**
     * Total Estimasi Biaya dari keseluruhan item di rencana ini.
     * Menggunakan rollup bertingkat dari akar-akar item (root items).
     */
    public function getTotalEstimatedAmountAttribute(): float
    {
        $roots = $this->relationLoaded('rootItems')
            ? $this->rootItems
            : $this->rootItems()->with('allChildren')->get();

        return (float) $roots->sum(fn ($root) => $root->effective_subtotal);
    }

    /**
     * Total Dana yang sudah terealisasi / item yang sudah terbeli.
     */
    public function getTotalCompletedAmountAttribute(): float
    {
        $roots = $this->relationLoaded('rootItems')
            ? $this->rootItems
            : $this->rootItems()->with('allChildren')->get();

        return (float) $roots->sum(fn ($root) => $root->effective_completed_subtotal);
    }

    /**
     * Sisa dana yang dibutuhkan untuk memenuhi seluruh item.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, $this->total_estimated_amount - $this->total_completed_amount);
    }

    /**
     * Persentase progres pembelian/penyelesaian rencana (0 - 100%).
     */
    public function getProgressPercentageAttribute(): float
    {
        $total = $this->total_estimated_amount;
        $completed = $this->total_completed_amount;

        if ($total > 0) {
            return (float) min(100.0, round(($completed / $total) * 100, 1));
        }

        $itemsCount = $this->total_items_count;
        if ($itemsCount > 0) {
            return (float) min(100.0, round(($this->completed_items_count / $itemsCount) * 100, 1));
        }

        return 0.0;
    }

    /**
     * Label Status Bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'Sedang Berjalan',
            self::STATUS_COMPLETED   => 'Selesai',
            self::STATUS_CANCELLED   => 'Dibatalkan',
            default                  => 'Direncanakan',
        };
    }

    /**
     * Badge visual status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'bg-blue-50 text-blue-700 border-blue-200',
            self::STATUS_COMPLETED   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::STATUS_CANCELLED   => 'bg-gray-100 text-gray-500 border-gray-200',
            default                  => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    }
}

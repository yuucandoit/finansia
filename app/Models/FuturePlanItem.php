<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class FuturePlanItem extends Model
{
    use HasFactory;

    protected $table = 'future_plan_items';

    protected $fillable = [
        'future_plan_id',
        'parent_id',
        'title',
        'quantity',
        'unit_price',
        'is_completed',
        'notes',
        'order',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'unit_price'     => 'decimal:2',
        'is_completed'   => 'boolean',
        'order'          => 'integer',
        'parent_id'      => 'integer',
        'future_plan_id' => 'integer',
    ];

    public function futurePlan(): BelongsTo
    {
        return $this->belongsTo(FuturePlan::class, 'future_plan_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FuturePlanItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FuturePlanItem::class, 'parent_id')
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Relasi rekursif untuk mengambil seluruh cabang anak secara bertingkat.
     */
    public function allChildren(): HasMany
    {
        return $this->hasMany(FuturePlanItem::class, 'parent_id')
            ->with('allChildren')
            ->orderBy('order')
            ->orderBy('id');
    }

    /**
     * Cek apakah item ini memiliki sub-item / anak.
     */
    public function hasChildren(): bool
    {
        if ($this->relationLoaded('allChildren')) {
            return $this->allChildren->isNotEmpty();
        }

        if ($this->relationLoaded('children')) {
            return $this->children->isNotEmpty();
        }

        return $this->children()->exists();
    }

    /**
     * Subtotal murni dari item ini: Qty * Unit Price.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quantity * (float) $this->unit_price);
    }

    /**
     * Rollup total efektif ala Excel WBS:
     * Jika memiliki sub-items, akumulasikan subtotal anak-anaknya (+ harga item ini jika ada).
     * Jika item daun (leaf), nilainya adalah Qty * Unit Price item ini sendiri.
     */
    public function getEffectiveSubtotalAttribute(): float
    {
        $children = $this->relationLoaded('allChildren')
            ? $this->allChildren
            : ($this->relationLoaded('children') ? $this->children : $this->children()->get());

        if ($children->isNotEmpty()) {
            $childrenTotal = (float) $children->sum(fn ($c) => $c->effective_subtotal);
            $ownTotal = $this->subtotal;
            return $ownTotal > 0 ? $ownTotal + $childrenTotal : $childrenTotal;
        }

        return $this->subtotal;
    }

    /**
     * Subtotal yang sudah berstatus selesai/terbeli.
     */
    public function getEffectiveCompletedSubtotalAttribute(): float
    {
        $children = $this->relationLoaded('allChildren')
            ? $this->allChildren
            : ($this->relationLoaded('children') ? $this->children : $this->children()->get());

        if ($children->isNotEmpty()) {
            $childrenTotal = (float) $children->sum(fn ($c) => $c->effective_completed_subtotal);
            $ownTotal = $this->is_completed ? $this->subtotal : 0.0;
            return $ownTotal + $childrenTotal;
        }

        return $this->is_completed ? $this->subtotal : 0.0;
    }

    /**
     * Mengambil seluruh item dalam satu plan secara flat terurut Depth-First
     * untuk dirender dalam baris tabel spreadsheet/task list bertingkat.
     */
    public static function getFlatTreeForPlan(int $planId, ?string $filter = null): Collection
    {
        $query = static::with('allChildren')
            ->where('future_plan_id', $planId)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('id');

        $roots = $query->get();
        $flatList = collect([]);

        foreach ($roots as $root) {
            static::flattenNode($root, 0, $flatList);
        }

        if ($filter === 'completed') {
            return $flatList->filter(fn ($item) => $item->is_completed)->values();
        }

        if ($filter === 'pending') {
            return $flatList->filter(fn ($item) => !$item->is_completed)->values();
        }

        return $flatList;
    }

    /**
     * Rekursif flattening node ke koleksi linear.
     */
    protected static function flattenNode(FuturePlanItem $node, int $depth, Collection &$result): void
    {
        $node->depth = $depth;
        $result->push($node);

        $children = $node->relationLoaded('allChildren')
            ? $node->allChildren
            : ($node->relationLoaded('children') ? $node->children : $node->children()->get());

        foreach ($children as $child) {
            static::flattenNode($child, $depth + 1, $result);
        }
    }
}


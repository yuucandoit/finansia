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
     * Ambil anak langsung, memakai relasi yang sudah di-load bila tersedia.
     */
    protected function loadedChildren(): Collection
    {
        if ($this->relationLoaded('allChildren')) {
            return $this->allChildren;
        }

        if ($this->relationLoaded('children')) {
            return $this->children;
        }

        return $this->children()->get();
    }

    /**
     * Item induk yang punya harga sendiri dan punya anak.
     * Harga induk diperlakukan sebagai Target / Plafon, bukan biaya tambahan.
     */
    public function getIsTargetAttribute(): bool
    {
        return $this->subtotal > 0 && $this->loadedChildren()->isNotEmpty();
    }

    /**
     * Total estimasi seluruh anak langsung (rekursif).
     */
    public function getChildrenTotalAttribute(): float
    {
        return (float) $this->loadedChildren()->sum(fn ($c) => $c->effective_subtotal);
    }

    /**
     * Total anak yang sudah terbeli (rekursif).
     */
    public function getChildrenCompletedTotalAttribute(): float
    {
        return (float) $this->loadedChildren()->sum(fn ($c) => $c->effective_completed_subtotal);
    }

    /**
     * Selisih rincian anak yang melebihi target induk (0 jika tidak over budget).
     */
    public function getOverBudgetAmountAttribute(): float
    {
        if (!$this->is_target) {
            return 0.0;
        }

        return max(0.0, $this->children_total - $this->subtotal);
    }

    /**
     * Total kebutuhan efektif:
     * - Item tunggal  : qty x harga.
     * - Grup          : jumlah seluruh anak.
     * - Target/Plafon : nilai terbesar antara target induk dan total rincian anak.
     */
    public function getEffectiveSubtotalAttribute(): float
    {
        $children = $this->loadedChildren();

        if ($children->isEmpty()) {
            return $this->subtotal;
        }

        $childrenTotal = (float) $children->sum(fn ($c) => $c->effective_subtotal);

        if ($this->subtotal > 0) {
            return max($this->subtotal, $childrenTotal);
        }

        return $childrenTotal;
    }

    /**
     * Total yang sudah terbeli/terpenuhi:
     * - Item tunggal : qty x harga jika dicentang.
     * - Induk dicentang : dianggap terpenuhi penuh sebesar kebutuhan efektif.
     * - Induk belum dicentang : jumlah anak yang terbeli, maksimal sebesar kebutuhan efektif.
     */
    public function getEffectiveCompletedSubtotalAttribute(): float
    {
        $children = $this->loadedChildren();

        if ($children->isEmpty()) {
            return $this->is_completed ? $this->subtotal : 0.0;
        }

        $effective = $this->effective_subtotal;

        if ($this->is_completed) {
            return $effective;
        }

        $childrenCompleted = (float) $children->sum(fn ($c) => $c->effective_completed_subtotal);

        return min($effective, $childrenCompleted);
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


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryRule extends Model
{
    protected $fillable = [
        'user_id',
        'keyword',
        'match_type',
        'category_id',
        'budget_item_id',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'user_id'        => 'integer',
        'category_id'    => 'integer',
        'budget_item_id' => 'integer',
        'priority'       => 'integer',
        'is_active'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class);
    }

    /**
     * Cek apakah deskripsi cocok dengan kata kunci rule ini (mendukung pemisah koma).
     */
    public function matches(string $description): bool
    {
        $descLower = strtolower(trim($description));
        if ($descLower === '') {
            return false;
        }

        $tokens = array_filter(array_map('trim', explode(',', strtolower($this->keyword))));
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            if ($this->match_type === 'exact') {
                if ($descLower === $token) {
                    return true;
                }
            } elseif (str_contains($descLower, $token)) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        'share_code',
    ];

    protected $casts = [
        'target_date' => 'date',
        'user_id'     => 'integer',
    ];

    public const STATUS_PLANNED     = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    protected static function booted(): void
    {
        static::creating(function (FuturePlan $plan) {
            if (empty($plan->share_code)) {
                $plan->share_code = static::generateUniqueShareCode();
            }
        });

        static::created(function (FuturePlan $plan) {
            // Daftarkan pembuat rencana sebagai anggota pemilik (owner)
            $plan->members()->syncWithoutDetaching([
                $plan->user_id => [
                    'role'           => 'owner',
                    'status'         => 'active',
                    'last_active_at' => now(),
                ]
            ]);
        });
    }

    public static function generateUniqueShareCode(): string
    {
        do {
            $code = 'FP-' . strtoupper(Str::random(6));
        } while (static::where('share_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'future_plan_members')
            ->withPivot(['role', 'status', 'last_active_at'])
            ->withTimestamps();
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
     * Scope untuk mengambil seluruh rencana yang dimiliki atau dibagikan ke pengguna.
     */
    public function scopeAccessibleBy($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhereHas('members', function ($mq) use ($userId) {
                    $mq->where('users.id', $userId)
                        ->where('future_plan_members.status', 'active');
                });
        });
    }

    /**
     * Memeriksa apakah seorang user memiliki hak akses (pembuat atau anggota aktif).
     */
    public function hasAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->members()
            ->where('users.id', $user->id)
            ->where('future_plan_members.status', 'active')
            ->exists();
    }

    /**
     * Memeriksa apakah user adalah pemilik rencana.
     */
    public function isOwner(?User $user): bool
    {
        return $user && $this->user_id === $user->id;
    }

    /**
     * Memperbarui timestamp aktivitas aktif pengguna.
     */
    public function touchActiveUser(User $user): void
    {
        $current = $this->members()->where('users.id', $user->id)->first();
        $role = $current ? ($current->pivot->role ?? 'editor') : ($this->isOwner($user) ? 'owner' : 'editor');

        $this->members()->syncWithoutDetaching([
            $user->id => [
                'role'           => $role,
                'status'         => 'active',
                'last_active_at' => now(),
            ]
        ]);
    }

    /**
     * Menambahkan user sebagai anggota rencana.
     */
    public function addMemberByUser(User $user, string $role = 'editor'): void
    {
        $role = $this->isOwner($user) ? 'owner' : $role;

        $this->members()->syncWithoutDetaching([
            $user->id => [
                'role'           => $role,
                'status'         => 'active',
                'last_active_at' => now(),
            ]
        ]);
    }

    /**
     * Mengambil daftar user yang sedang aktif (terdeteksi dalam 3 menit terakhir).
     */
    public function getActiveUsers(): Collection
    {
        $activeMembers = $this->members()
            ->where('future_plan_members.status', 'active')
            ->where('future_plan_members.last_active_at', '>=', now()->subMinutes(3))
            ->get();

        return $activeMembers->map(function ($u) {
            $name = trim($u->name ?: 'User');
            $initial = strtoupper(mb_substr($name, 0, 1));
            $colors = [
                'bg-indigo-600',
                'bg-emerald-600',
                'bg-violet-600',
                'bg-rose-600',
                'bg-sky-600',
                'bg-amber-600',
                'bg-teal-600',
                'bg-fuchsia-600',
            ];
            $colorClass = $colors[$u->id % count($colors)];

            return [
                'id'             => $u->id,
                'name'           => $name,
                'email'          => $u->email,
                'initial'        => $initial,
                'color'          => $colorClass,
                'role'           => $u->pivot->role ?? ($this->isOwner($u) ? 'owner' : 'editor'),
                'role_label'     => (($u->pivot->role ?? '') === 'owner' || $this->isOwner($u)) ? 'Pemilik' : 'Anggota',
                'is_owner'       => $this->isOwner($u),
                'is_current'     => auth()->id() === $u->id,
                'last_active_at' => $u->pivot->last_active_at ? Carbon::parse($u->pivot->last_active_at)->diffForHumans() : 'baru saja',
            ];
        });
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

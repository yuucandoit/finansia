<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'telegram_chat_id',
        'telegram_link_code',
        'telegram_notifications_enabled',
        'plan_tier',
        'plan_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telegram_notifications_enabled' => 'boolean',
            'plan_expires_at' => 'datetime',
        ];
    }

    public function savings()
    {
        return $this->hasMany(Saving::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function futurePlans()
    {
        return $this->hasMany(FuturePlan::class);
    }

    public function sharedFuturePlans()
    {
        return $this->belongsToMany(FuturePlan::class, 'future_plan_members')
            ->withPivot(['role', 'status', 'last_active_at'])
            ->withTimestamps();
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function budgetItems()
    {
        return $this->hasManyThrough(BudgetItem::class, Budget::class);
    }

    public function isMasterMind(): bool
    {
        return $this->role === 'admin' || strtolower((string) $this->email) === 'wahyusnjy@gmail.com';
    }

    public function isApproved(): bool
    {
        return $this->isMasterMind() || $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public static function pendingApprovalsCount(): int
    {
        return static::where('status', 'pending')->count();
    }

    public function ensureDefaultCashSaving(): Saving
    {
        $cashBank = Bank::firstOrCreate(
            ['code' => '000'],
            ['name' => 'Tunai / Cash']
        );

        return $this->savings()->firstOrCreate(
            ['account_number' => 'CASH'],
            [
                'name' => 'Dompet Tunai (Cash)',
                'bank_id' => $cashBank->id,
                'current_balance' => 0,
            ]
        );
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function aiUsageLogs()
    {
        return $this->hasMany(AiUsageLog::class);
    }

    public function isPro(): bool
    {
        if (in_array($this->role, ['admin', 'master_mind'], true)) {
            return true;
        }

        if ($this->plan_tier !== 'pro') {
            return false;
        }

        return $this->plan_expires_at === null || $this->plan_expires_at->isFuture();
    }

    public function monthlyAiUsageCount(): int
    {
        return (int) $this->aiUsageLogs()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    public function remainingAiQuota(): ?int
    {
        if ($this->isPro()) {
            return null;
        }

        $limit = (int) config('saas.plans.free.monthly_ai_limit', 30);

        return max(0, $limit - $this->monthlyAiUsageCount());
    }

    public function canUseAi(): bool
    {
        if (!config('saas.enforcement_enabled', false)) {
            return true;
        }

        if ($this->isPro()) {
            return true;
        }

        return ($this->remainingAiQuota() ?? 0) > 0;
    }
}

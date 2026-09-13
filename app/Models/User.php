<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;
    protected $table = 'savings';
    protected $fillable = [
        'id',
        'user_id',
        'bank_id',
        'name',
        'account_number',
        'current_balance',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class,'savings_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'saving_members', 'saving_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isAccessibleBy(User $user): bool
    {
        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function canRecordBy(User $user): bool
    {
        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        return $this->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'editor')
            ->exists();
    }
}

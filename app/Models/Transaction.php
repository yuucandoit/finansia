<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    protected $fillable = [
        'user_id',
        'savings_id',
        'category_id',
        'type',
        'description',
        'amount',
        'fee',
        'is_manual',
        'transaction_date',
        'attachment',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function savingAccount()
    {
        return $this->belongsTo(Saving::class, 'savings_id');
    }
}

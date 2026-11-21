<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $table = 'recurring_transactions';

    protected $fillable = [
        'user_id',
        'savings_id',
        'category_id',
        'type',
        'description',
        'amount',
        'frequency',
        'next_run_date',
        'last_run_date',
        'is_active',
    ];

    protected $casts = [
        'user_id'       => 'integer',
        'savings_id'    => 'integer',
        'category_id'   => 'integer',
        'amount'        => 'decimal:2',
        'next_run_date' => 'date:Y-m-d',
        'last_run_date' => 'date:Y-m-d',
        'is_active'     => 'boolean',
    ];

    public const FREQUENCIES = [
        'daily'   => 'Harian',
        'weekly'  => 'Mingguan',
        'monthly' => 'Bulanan',
        'yearly'  => 'Tahunan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savingAccount(): BelongsTo
    {
        return $this->belongsTo(Saving::class, 'savings_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? ucfirst($this->frequency);
    }

    /**
     * Hitung tanggal eksekusi berikutnya berdasarkan frekuensi.
     */
    public function computeNextRunDate(Carbon $fromDate): Carbon
    {
        return match ($this->frequency) {
            'daily'   => $fromDate->copy()->addDay(),
            'weekly'  => $fromDate->copy()->addWeek(),
            'yearly'  => $fromDate->copy()->addYear(),
            default   => $fromDate->copy()->addMonthNoOverflow(),
        };
    }

    /**
     * Jalankan transaksi berulang sekarang: buat record Transaction, mutasikan saldo rekening,
     * dan majukan next_run_date.
     */
    public function executeNow(?Carbon $runDate = null): Transaction
    {
        $runDate = ($runDate ?? now())->copy()->startOfDay();

        return DB::transaction(function () use ($runDate) {
            $saving = Saving::where('user_id', $this->user_id)
                ->lockForUpdate()
                ->findOrFail($this->savings_id);

            $amount = (float) $this->amount;
            if ($this->type === 'Cash Out' && (float) $saving->current_balance < $amount) {
                throw new \Exception("Saldo rekening {$saving->name} tidak mencukupi untuk transaksi rutin \"{$this->description}\".");
            }

            $tx = Transaction::create([
                'user_id'          => $this->user_id,
                'savings_id'       => $saving->id,
                'category_id'      => $this->category_id,
                'type'             => $this->type,
                'direction'        => $this->type === 'Cash In' ? Transaction::DIRECTION_IN : Transaction::DIRECTION_OUT,
                'description'      => $this->description . ' (Rutin)',
                'amount'           => $amount,
                'fee'              => null,
                'transaction_date' => $runDate->toDateString(),
                'is_manual'        => false,
            ]);

            if ($this->type === 'Cash In') {
                $saving->increment('current_balance', $amount);
            } else {
                $saving->decrement('current_balance', $amount);
            }

            $baseDate = $this->next_run_date ? Carbon::parse($this->next_run_date) : $runDate;
            if ($baseDate->lt($runDate)) {
                $baseDate = $runDate;
            }

            $this->update([
                'last_run_date' => $runDate->toDateString(),
                'next_run_date' => $this->computeNextRunDate($baseDate)->toDateString(),
            ]);

            return $tx;
        });
    }
}

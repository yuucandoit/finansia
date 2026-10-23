<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Arah uang terhadap rekening (savings_id): 'in' menambah saldo, 'out' mengurangi saldo.
            $table->string('direction', 3)->default('out')->after('type');
            // Menghubungkan dua sisi transfer antar rekening milik user sendiri.
            $table->uuid('transfer_group_id')->nullable()->after('direction');
            $table->index(['user_id', 'transaction_date']);
            $table->index('transfer_group_id');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'transaction_date']);
            $table->dropIndex(['transfer_group_id']);
            $table->dropColumn(['direction', 'transfer_group_id']);
        });
    }

    /**
     * Isi kolom baru untuk data lama.
     * - Cash In -> in, selainnya -> out
     * - Kaki tujuan transfer (deskripsi berisi "(dari ...)") -> in
     * - Pasangkan kaki "(ke ...)" dan "(dari ...)" dengan transfer_group_id yang sama
     */
    private function backfill(): void
    {
        DB::table('transactions')->where('type', 'Cash In')->update(['direction' => 'in']);

        DB::table('transactions')
            ->whereIn('type', ['Transfer', 'Tabungan'])
            ->where('description', 'like', '%(dari %)')
            ->update(['direction' => 'in']);

        $incomingLegs = DB::table('transactions')
            ->whereIn('type', ['Transfer', 'Tabungan'])
            ->where('direction', 'in')
            ->whereNull('transfer_group_id')
            ->whereNull('deleted_at')
            ->get();

        foreach ($incomingLegs as $in) {
            $prefix = trim(preg_replace('/\s*\(dari [^)]*\)\s*$/', '', $in->description));

            $out = DB::table('transactions')
                ->where('user_id', $in->user_id)
                ->where('type', $in->type)
                ->where('direction', 'out')
                ->whereNull('transfer_group_id')
                ->whereNull('deleted_at')
                ->where('amount', $in->amount)
                ->whereDate('transaction_date', $in->transaction_date)
                ->where('savings_id', '!=', $in->savings_id)
                ->where('description', 'like', $prefix . ' (ke %')
                ->orderBy('id')
                ->first();

            if ($out) {
                $groupId = (string) Str::uuid();
                DB::table('transactions')->whereIn('id', [$in->id, $out->id])->update(['transfer_group_id' => $groupId]);
            }
        }
    }
};


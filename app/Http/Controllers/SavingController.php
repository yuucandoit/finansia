<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $savings = Saving::where('user_id', Auth::id())
            ->with('bank')
            ->orderBy('created_at', 'DESC')
            ->get();
        
        $totalBalance = $savings->sum('current_balance');
        $banks = Bank::orderBy('name')->get();
        
        return view('saving', compact('savings', 'totalBalance', 'banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'current_balance' => 'required|numeric|min:0',
        ]);

        Saving::create([
            'user_id' => Auth::id(),
            'bank_id' => $validated['bank_id'],
            'name' => $validated['name'],
            'account_number' => $validated['account_number'],
            'current_balance' => $validated['current_balance'] ? $validated['current_balance'] : 0,
        ]);

        return redirect()->route('saving.index')->with('success', 'Tabungan berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $saving = Saving::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'current_balance' => 'required|numeric|min:0',
        ]);

        $saving->update($validated);

        return redirect()->route('saving.index')->with('success', 'Tabungan berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $saving = Saving::where('user_id', Auth::id())->findOrFail($id);
        $saving->delete();

        return redirect()->route('saving.index')->with('success', 'Tabungan berhasil dihapus!');
    }

    /**
     * Reconcile / adjust saving account balance to match actual bank balance.
     */
    public function reconcile(Request $request, $id)
    {
        $saving = Saving::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'actual_balance' => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:255',
        ]);

        $actualBalance = (float) $validated['actual_balance'];
        $currentBalance = (float) $saving->current_balance;
        $diff = round($actualBalance - $currentBalance, 2);

        if (abs($diff) < 0.01) {
            return redirect()->back()->with('success', 'Saldo rekening sudah sesuai dengan saldo aktual.');
        }

        DB::transaction(function () use ($saving, $actualBalance, $diff, $validated) {
            $isPositive = $diff > 0;
            $defaultDesc = $isPositive
                ? 'Penyesuaian Saldo (Rekonsiliasi +)'
                : 'Penyesuaian Saldo (Rekonsiliasi -)';

            $description = !empty($validated['notes'])
                ? 'Rekonsiliasi Saldo: ' . trim($validated['notes'])
                : $defaultDesc;

            Transaction::create([
                'user_id'          => Auth::id(),
                'savings_id'       => $saving->id,
                'type'             => $isPositive ? 'Cash In' : 'Cash Out',
                'direction'        => $isPositive ? Transaction::DIRECTION_IN : Transaction::DIRECTION_OUT,
                'description'      => $description,
                'amount'           => abs($diff),
                'fee'              => null,
                'transaction_date' => now()->toDateString(),
                'is_manual'        => true,
            ]);

            $saving->update([
                'current_balance' => $actualBalance,
            ]);
        });

        return redirect()->back()->with(
            'success',
            'Rekonsiliasi berhasil! Selisih Rp ' . number_format(abs($diff), 0, ',', '.') . ' telah dicatat otomatis ke riwayat transaksi.'
        );
    }

    public function detail(Request $request, $id)
    {
        $year = (int) ($request->year ?? date('Y'));
        $saving = Saving::with('bank')
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$saving) {
            abort(404);
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExtract = $isSqlite ? "CAST(strftime('%Y', transaction_date) AS INTEGER)" : "EXTRACT(YEAR FROM transaction_date)";
        $monthExtract = $isSqlite ? "CAST(strftime('%m', transaction_date) AS INTEGER)" : "EXTRACT(MONTH FROM transaction_date)";

        $transaction_query = $saving->transaction();
        $years = $saving->transaction()
            ->selectRaw("{$yearExtract} as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty() || !$years->contains($year)) {
            $years = $years->push((int) date('Y'))->unique()->sortDesc()->values();
        }

        // Statistik Tahunan (dipertahankan untuk kompatibilitas & grafik tahunan)
        $yearly_income = (clone $transaction_query)
            ->where('type', 'Cash In')
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $yearly_spending = (clone $transaction_query)
            ->where('type', 'Cash Out')
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $income_vs_spending = (clone $transaction_query)
            ->selectRaw("{$monthExtract} as month, 
                    SUM(CASE WHEN type = 'Cash In' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN type = 'Cash Out' THEN amount ELSE 0 END) as spending")
            ->whereYear('transaction_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $current_balance = (float) $saving->current_balance;

        $diff_from_future = $saving->transaction()
            ->where('transaction_date', '>', $year . '-12-31')
            ->selectRaw("SUM(CASE WHEN type = 'Cash In' THEN amount ELSE 0 END) - 
                        SUM(CASE WHEN type = 'Cash Out' THEN amount ELSE 0 END) as diff")
            ->value('diff') ?? 0;

        $trend_saldo = $current_balance - $diff_from_future;

        // ─────────────────────────────────────────────────────────────────
        // FILTER PERIODE FLEKSIBEL: daily, weekly, monthly, yearly, range
        // ─────────────────────────────────────────────────────────────────
        $period = $request->input('period', 'yearly');
        if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly', 'range'], true)) {
            $period = 'yearly';
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $selectedDate = $request->input('date', now()->toDateString());
        $selectedMonth = (int) $request->input('month', now()->month);
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) now()->month;
        }
        $selectedWeekDate = $request->input('week_date', now()->toDateString());
        $rangeStartInput = $request->input('start_date', now()->startOfMonth()->toDateString());
        $rangeEndInput = $request->input('end_date', now()->toDateString());

        switch ($period) {
            case 'daily':
                $startDate = \Carbon\Carbon::parse($selectedDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($selectedDate)->endOfDay();
                $periodLabel = 'Harian: ' . $startDate->format('d') . ' ' . ($monthNames[(int) $startDate->format('n')] ?? $startDate->format('M')) . ' ' . $startDate->format('Y');
                break;

            case 'weekly':
                $refDate = \Carbon\Carbon::parse($selectedWeekDate);
                $startDate = $refDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->startOfDay();
                $endDate = $startDate->copy()->addDays(6)->endOfDay();
                $periodLabel = 'Mingguan: ' . $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y');
                break;

            case 'monthly':
                $startDate = \Carbon\Carbon::createFromDate($year, $selectedMonth, 1)->startOfMonth()->startOfDay();
                $endDate = $startDate->copy()->endOfMonth()->endOfDay();
                $periodLabel = 'Bulanan: ' . ($monthNames[$selectedMonth] ?? '') . ' ' . $year;
                break;

            case 'range':
                $startDate = \Carbon\Carbon::parse($rangeStartInput)->startOfDay();
                $endDate = \Carbon\Carbon::parse($rangeEndInput)->endOfDay();
                if ($endDate->lt($startDate)) {
                    [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
                }
                $periodLabel = 'Rentang Tanggal: ' . $startDate->format('d M Y') . ' – ' . $endDate->format('d M Y');
                break;

            case 'yearly':
            default:
                $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear()->startOfDay();
                $endDate = \Carbon\Carbon::createFromDate($year, 12, 31)->endOfYear()->endOfDay();
                $periodLabel = 'Tahunan: ' . $year;
                break;
        }

        // Ambil seluruh transaksi pada periode terpilih beserta kategorinya
        $txQuery = Transaction::with('category')
            ->where('savings_id', $saving->id)
            ->whereDate('transaction_date', '>=', $startDate->toDateString())
            ->whereDate('transaction_date', '<=', $endDate->toDateString());

        $typeFilter = $request->input('tx_type');
        $searchQuery = trim((string) $request->input('search', ''));

        $allPeriodTransactions = (clone $txQuery)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        // Filter untuk tabel rincian jika user mencari kata kunci atau filter tipe
        $filteredTransactions = $allPeriodTransactions->filter(function (Transaction $tx) use ($typeFilter, $searchQuery) {
            if ($typeFilter === 'in' && $tx->balanceEffect() < 0) {
                return false;
            }
            if ($typeFilter === 'out' && $tx->balanceEffect() >= 0) {
                return false;
            }
            if ($searchQuery !== '') {
                $haystack = strtolower(($tx->description ?? '') . ' ' . ($tx->category->name ?? '') . ' ' . $tx->type);
                if (!str_contains($haystack, strtolower($searchQuery))) {
                    return false;
                }
            }
            return true;
        })->values();

        // ─────────────────────────────────────────────────────────────────
        // ANALITIK "UANGNYA KEMANA AJA?" (WHERE DID THE MONEY GO?)
        // ─────────────────────────────────────────────────────────────────
        $inflowTransactions = $allPeriodTransactions->filter(fn (Transaction $tx) => $tx->balanceEffect() > 0);
        $outflowTransactions = $allPeriodTransactions->filter(fn (Transaction $tx) => $tx->balanceEffect() < 0);

        $periodIncome = (float) $inflowTransactions->sum(fn (Transaction $tx) => (float) $tx->amount);
        $periodSpending = (float) $outflowTransactions->sum(fn (Transaction $tx) => (float) $tx->amount + (float) ($tx->fee ?? 0));
        $periodFeeTotal = (float) $outflowTransactions->sum(fn (Transaction $tx) => (float) ($tx->fee ?? 0));
        $periodNetCashflow = $periodIncome - $periodSpending;

        $effectiveEndForAvg = $endDate->isFuture() ? now()->endOfDay() : $endDate;
        $daysInPeriod = max(1, (int) $startDate->copy()->startOfDay()->diffInDays($effectiveEndForAvg->copy()->startOfDay()) + 1);
        $avgDailySpending = $periodSpending / $daysInPeriod;

        // 1. Breakdown Pengeluaran per Kategori
        $spendingByCategory = $outflowTransactions
            ->groupBy(function (Transaction $tx) {
                if ($tx->type === 'Transfer') {
                    return 'Transfer Keluar';
                }
                if ($tx->type === 'Tabungan') {
                    return 'Alokasi Tabungan';
                }
                return $tx->category->name ?? 'Tanpa Kategori';
            })
            ->map(function ($group, $categoryName) use ($periodSpending) {
                $totalAmount = (float) $group->sum(fn (Transaction $tx) => (float) $tx->amount + (float) ($tx->fee ?? 0));
                $firstTx = $group->first();
                $color = $firstTx?->category?->color ?? ($categoryName === 'Transfer Keluar' ? 'blue' : ($categoryName === 'Alokasi Tabungan' ? 'indigo' : 'gray'));
                $biggestTx = $group->sortByDesc(fn (Transaction $tx) => (float) $tx->amount + (float) ($tx->fee ?? 0))->first();

                return [
                    'category'     => $categoryName,
                    'total'        => $totalAmount,
                    'count'        => $group->count(),
                    'percentage'   => $periodSpending > 0 ? round(($totalAmount / $periodSpending) * 100, 1) : 0,
                    'color'        => $color,
                    'top_tx_desc'  => $biggestTx?->description ?? '-',
                    'top_tx_total' => $biggestTx ? ((float) $biggestTx->amount + (float) ($biggestTx->fee ?? 0)) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // 2. Breakdown Pemasukan per Sumber/Kategori
        $incomeByCategory = $inflowTransactions
            ->groupBy(function (Transaction $tx) {
                if ($tx->type === 'Transfer') {
                    return 'Transfer Masuk';
                }
                if ($tx->type === 'Tabungan') {
                    return 'Setoran Tabungan';
                }
                return $tx->category->name ?? 'Pemasukan Lainnya';
            })
            ->map(function ($group, $categoryName) use ($periodIncome) {
                $totalAmount = (float) $group->sum(fn (Transaction $tx) => (float) $tx->amount);
                return [
                    'category'   => $categoryName,
                    'total'      => $totalAmount,
                    'count'      => $group->count(),
                    'percentage' => $periodIncome > 0 ? round(($totalAmount / $periodIncome) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // 3. Top 5 Pengeluaran Terbesar di Periode Ini
        $topExpenses = $outflowTransactions
            ->sortByDesc(fn (Transaction $tx) => (float) $tx->amount + (float) ($tx->fee ?? 0))
            ->take(5)
            ->values();

        // 4. Pos Pengeluaran Paling Sering Muncul (Frekuensi Tertinggi)
        $frequentExpenses = $outflowTransactions
            ->groupBy(fn (Transaction $tx) => strtolower(trim($tx->description ?: 'Transaksi')))
            ->map(function ($group) use ($periodSpending) {
                $first = $group->first();
                $total = (float) $group->sum(fn (Transaction $tx) => (float) $tx->amount + (float) ($tx->fee ?? 0));
                return [
                    'description' => $first->description ?: 'Transaksi',
                    'category'    => $first->category->name ?? ($first->type === 'Transfer' ? 'Transfer' : 'Lainnya'),
                    'count'       => $group->count(),
                    'total'       => $total,
                    'percentage'  => $periodSpending > 0 ? round(($total / $periodSpending) * 100, 1) : 0,
                ];
            })
            ->sortByDesc(fn ($item) => [$item['count'], $item['total']])
            ->take(5)
            ->values();

        // 5. Alokasi Arus Keluar (Pengeluaran Murni vs Transfer Internal vs Tabungan vs Biaya Admin)
        $pureExpenseAmount = (float) $outflowTransactions->where('type', 'Cash Out')->sum('amount');
        $transferOutAmount = (float) $outflowTransactions->where('type', 'Transfer')->sum('amount');
        $savingOutAmount = (float) $outflowTransactions->where('type', 'Tabungan')->sum('amount');

        $outflowAllocation = [
            'pure_expense' => $pureExpenseAmount,
            'transfer_out' => $transferOutAmount,
            'saving_out'   => $savingOutAmount,
            'fee_total'    => $periodFeeTotal,
        ];

        $categories = \App\Models\Category::forUser(Auth::id())->orderBy('name')->get();
        $allUserSavings = Saving::where('user_id', Auth::id())->orderBy('name')->get();

        return view('saving_detail', compact(
            'saving',
            'yearly_income',
            'yearly_spending',
            'income_vs_spending',
            'trend_saldo',
            'years',
            'year',
            'period',
            'periodLabel',
            'startDate',
            'endDate',
            'selectedDate',
            'selectedMonth',
            'selectedWeekDate',
            'monthNames',
            'typeFilter',
            'searchQuery',
            'filteredTransactions',
            'allPeriodTransactions',
            'periodIncome',
            'periodSpending',
            'periodFeeTotal',
            'periodNetCashflow',
            'daysInPeriod',
            'avgDailySpending',
            'spendingByCategory',
            'incomeByCategory',
            'topExpenses',
            'frequentExpenses',
            'outflowAllocation',
            'categories',
            'allUserSavings'
        ));
    }

    public function getMonthsApi(Request $request, $id)
    {
        $year = $request->year ?? date('Y');
        
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExtract = $isSqlite ? "CAST(strftime('%m', transaction_date) AS INTEGER)" : "EXTRACT(MONTH FROM transaction_date)";
        $monthNameSql = $isSqlite ? "strftime('%m', transaction_date)" : "TO_CHAR(transaction_date, 'Month')";

        $months = Transaction::where('savings_id', $id)
            ->whereYear('transaction_date', $year)
            ->selectRaw("{$monthExtract} as month_num, 
                        {$monthNameSql} as month_name,
                        COUNT(*) as total_count")
            ->groupBy('month_num', 'month_name')
            ->orderBy('month_num', 'desc')
            ->get();

        return response()->json($months);
    }

    public function getTransactionApi(Request $request, $id)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month;
        // Validasi
        if(empty($year)){
            return response()->json([
                'status' => 'error',
                'message' => 'Year are required'
            ]);
        }elseif(empty($month)){
            return response()->json([
                'status' => 'error',
                'message' => 'Month are required'
            ]);
        }
        

        $transactions = Transaction::where('savings_id',$id)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->orderBy('transaction_date', 'DESC')
            ->get() // Kita ambil semua untuk grouping (atau gunakan pagination jika data ribuan)
            ->groupBy(function($date) {
                return \Carbon\Carbon::parse($date->transaction_date)->format('F'); // Group by "January", "February"
            });

        return response()->json($transactions);
    }
    
}

<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Saving;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        // Total Saldo dari semua tabungan
        $totalBalance = Saving::where('user_id', $userId)->sum('current_balance');
        
        // Cash In & Cash Out bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $cashInThisMonth = Transaction::where('user_id', $userId)
            ->where('type', 'Cash In')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
            
        $cashOutThisMonth = Transaction::where('user_id', $userId)
            ->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
        
        // Data untuk Bar Chart - Cash Flow 7 hari terakhir
        $last7Days = [];
        $cashInData = [];
        $cashOutData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days[] = $date->format('d M');
            
            $cashIn = Transaction::where('user_id', $userId)
                ->where('type', 'Cash In')
                ->whereDate('transaction_date', $date)
                ->sum('amount');
            $cashInData[] = $cashIn;
            
            $cashOut = Transaction::where('user_id', $userId)
                ->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])
                ->whereDate('transaction_date', $date)
                ->sum('amount');
            $cashOutData[] = $cashOut;
        }
        
        // Data untuk Donut Chart - Top 5 Kategori Pengeluaran Bulan Ini
        // Gunakan description sebagai kategori
        $topExpenses = Transaction::where('user_id', $userId)
            ->whereIn('type', ['Cash Out'])
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->select('description', DB::raw('SUM(amount) as total'))
            ->groupBy('description')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        
        $expenseLabels = $topExpenses->pluck('description')->toArray();
        $expenseData = $topExpenses->pluck('total')->toArray();
        
        // Data untuk Line Chart - Trend Saldo 30 hari terakhir
        $last30Days = [];
        $balanceTrend = [];
        $runningBalance = $totalBalance;
        
        // Hitung saldo dari hari ini mundur ke 30 hari lalu
        for ($i = 0; $i <= 29; $i++) {
            $date = Carbon::now()->subDays($i);
            
            // Transaksi pada hari ini
            $dayTransactions = Transaction::where('user_id', $userId)
                ->whereDate('transaction_date', $date)
                ->get();
            
            // Kurangi saldo dengan transaksi hari ini (karena kita mundur)
            foreach ($dayTransactions as $trans) {
                if ($trans->type == 'Cash In') {
                    $runningBalance -= $trans->amount;
                } else {
                    $runningBalance += $trans->amount;
                }
            }
            
            // Simpan untuk ditampilkan terbalik nanti
            $last30Days[] = $date->format('d M');
            $balanceTrend[] = $runningBalance;
        }
        
        // Balik array agar urut dari 30 hari lalu ke hari ini
        $last30Days = array_reverse($last30Days);
        $balanceTrend = array_reverse($balanceTrend);
        
        // Transaksi terakhir
        $recentTransactions = Transaction::where('user_id', $userId)
            ->orderBy('transaction_date','DESC')
            ->limit(5)
            ->get();
        
        return view('dashboard', [
            'totalBalance' => $totalBalance,
            'cashInThisMonth' => $cashInThisMonth,
            'cashOutThisMonth' => $cashOutThisMonth,
            'last7Days' => $last7Days,
            'cashInData' => $cashInData,
            'cashOutData' => $cashOutData,
            'expenseLabels' => $expenseLabels,
            'expenseData' => $expenseData,
            'last30Days' => $last30Days,
            'balanceTrend' => $balanceTrend,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}

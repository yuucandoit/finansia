<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Mengambil ringkasan data dashboard untuk mobile apps.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // 1. Total Saldo
        $totalBalance = (float) Saving::where('user_id', $userId)->sum('current_balance');

        // 2. Cash In & Cash Out bulan berjalan
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $cashInThisMonth = (float) Transaction::where('user_id', $userId)
            ->where('type', 'Cash In')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $cashOutThisMonth = (float) Transaction::where('user_id', $userId)
            ->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // 3. Cash Flow 7 Hari Terakhir
        $last7Days = [];
        $cashInData = [];
        $cashOutData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days[] = $date->translatedFormat('d M');

            $cashIn = (float) Transaction::where('user_id', $userId)
                ->where('type', 'Cash In')
                ->whereDate('transaction_date', $date)
                ->sum('amount');
            $cashInData[] = $cashIn;

            $cashOut = (float) Transaction::where('user_id', $userId)
                ->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])
                ->whereDate('transaction_date', $date)
                ->sum('amount');
            $cashOutData[] = $cashOut;
        }

        // 4. Top 5 Kategori Pengeluaran Bulan Ini
        $topExpenses = Transaction::leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->whereIn('transactions.type', ['Cash Out'])
            ->whereBetween('transactions.transaction_date', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw("COALESCE(categories.name, 'Lainnya') as category_name"),
                DB::raw("COALESCE(categories.color, 'gray') as category_color"),
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('category_name', 'category_color')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $expenseCategories = $topExpenses->map(function ($item) use ($cashOutThisMonth) {
            $total = (float) $item->total;
            $percentage = $cashOutThisMonth > 0 ? round(($total / $cashOutThisMonth) * 100, 1) : 0;

            return [
                'name'            => $item->category_name,
                'color'           => $item->category_color,
                'total'           => $total,
                'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
                'percentage'      => $percentage,
            ];
        });

        // 5. Trend Saldo 30 Hari Terakhir
        $last30Days = [];
        $balanceTrend = [];
        $runningBalance = $totalBalance;

        for ($i = 0; $i <= 29; $i++) {
            $date = Carbon::now()->subDays($i);

            $dayTransactions = Transaction::where('user_id', $userId)
                ->whereDate('transaction_date', $date)
                ->get();

            foreach ($dayTransactions as $trans) {
                if ($trans->type === 'Cash In') {
                    $runningBalance -= (float) $trans->amount;
                } else {
                    $runningBalance += (float) $trans->amount;
                }
            }

            $last30Days[] = $date->translatedFormat('d M');
            $balanceTrend[] = (float) $runningBalance;
        }

        $last30Days = array_reverse($last30Days);
        $balanceTrend = array_reverse($balanceTrend);

        // 6. Transaksi Terbaru (5 item)
        $recentTransactions = Transaction::with(['category', 'savingAccount'])
            ->where('user_id', $userId)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->map(function ($tx) {
                return [
                    'id'               => $tx->id,
                    'type'             => $tx->type,
                    'amount'           => (float) $tx->amount,
                    'amount_formatted' => 'Rp ' . number_format($tx->amount, 0, ',', '.'),
                    'description'      => $tx->description,
                    'transaction_date' => $tx->transaction_date ? Carbon::parse($tx->transaction_date)->translatedFormat('d M Y, H:i') : null,
                    'category'         => $tx->category ? [
                        'id'    => $tx->category->id,
                        'name'  => $tx->category->name,
                        'color' => $tx->category->color,
                    ] : null,
                    'account'          => $tx->savingAccount ? [
                        'id'   => $tx->savingAccount->id,
                        'name' => $tx->savingAccount->name,
                    ] : null,
                ];
            });

        return $this->successResponse([
            'summary' => [
                'total_balance'           => $totalBalance,
                'total_balance_formatted' => 'Rp ' . number_format($totalBalance, 0, ',', '.'),
                'cash_in_this_month'      => $cashInThisMonth,
                'cash_in_formatted'       => 'Rp ' . number_format($cashInThisMonth, 0, ',', '.'),
                'cash_out_this_month'     => $cashOutThisMonth,
                'cash_out_formatted'      => 'Rp ' . number_format($cashOutThisMonth, 0, ',', '.'),
            ],
            'charts' => [
                'cash_flow_7_days' => [
                    'labels'   => $last7Days,
                    'cash_in'  => $cashInData,
                    'cash_out' => $cashOutData,
                ],
                'expense_categories' => $expenseCategories,
                'balance_trend_30_days' => [
                    'labels' => $last30Days,
                    'data'   => $balanceTrend,
                ],
            ],
            'recent_transactions' => $recentTransactions,
        ], 'Data dashboard berhasil diambil.');
    }
}

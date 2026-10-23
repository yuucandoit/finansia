<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $user = auth()->user();
        $userId = $user->id;

        $dashboard = $dashboardService->build($user);

        $charts = $dashboard['charts'];
        $spending = collect($dashboard['spending_by_category']);

        // Transaksi terakhir
        $recentTransactions = Transaction::with(['category', 'savingAccount'])
            ->where('user_id', $userId)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get();

        // Savings accounts & categories untuk fitur import dan transaksi
        $savings = Saving::where('user_id', $userId)->with('bank')->get();
        $categories = Category::orderBy('name')->get();
        $activeBudget = Budget::with('items.category')
            ->where('user_id', $userId)
            ->where('month', (int) now()->month)
            ->where('year', (int) now()->year)
            ->first();
        $budgetItems = $activeBudget ? $activeBudget->items : collect([]);

        return view('dashboard', [
            'dashboard'          => $dashboard,
            'totalBalance'       => $dashboard['net_worth']['current'],
            'cashInThisMonth'    => $dashboard['cashflow']['income']['amount'],
            'cashOutThisMonth'   => $dashboard['cashflow']['expense']['amount'],
            'last7Days'          => $charts['cash_flow_7_days']['labels'],
            'cashInData'         => $charts['cash_flow_7_days']['cash_in'],
            'cashOutData'        => $charts['cash_flow_7_days']['cash_out'],
            'expenseLabels'      => $spending->pluck('name')->all(),
            'expenseData'        => $spending->pluck('amount')->all(),
            'last30Days'         => $charts['daily_balance_30']['labels'],
            'balanceTrend'       => $charts['daily_balance_30']['data'],
            'recentTransactions' => $recentTransactions,
            'savings'            => $savings,
            'categories'         => $categories,
            'budgetItems'        => $budgetItems,
        ]);
    }
}

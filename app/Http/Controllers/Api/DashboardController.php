<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Mengambil ringkasan data dashboard untuk mobile apps.
     */
    public function index(Request $request, DashboardService $dashboardService): JsonResponse
    {
        $user = $request->user();
        $dashboard = $dashboardService->build($user);

        $totalBalance = $dashboard['net_worth']['current'];
        $cashIn = $dashboard['cashflow']['income']['amount'];
        $cashOut = $dashboard['cashflow']['expense']['amount'];
        $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

        $recentTransactions = Transaction::with(['category', 'savingAccount'])
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->map(fn ($tx) => [
                'id'                => $tx->id,
                'type'              => $tx->type,
                'direction'         => $tx->direction,
                'flow'              => $tx->flowGroup(),
                'amount'            => (float) $tx->amount,
                'amount_formatted'  => $rp($tx->amount),
                'description'       => $tx->description,
                'transaction_date'  => $tx->transaction_date ? Carbon::parse($tx->transaction_date)->translatedFormat('d M Y') : null,
                'category'          => $tx->category ? [
                    'id'    => $tx->category->id,
                    'name'  => $tx->category->name,
                    'color' => $tx->category->color,
                ] : null,
                'account'           => $tx->savingAccount ? [
                    'id'   => $tx->savingAccount->id,
                    'name' => $tx->savingAccount->name,
                ] : null,
            ]);

        // Format lama (summary & charts) dipertahankan agar aplikasi mobile versi lama tetap jalan
        $legacyExpenseCategories = collect($dashboard['spending_by_category'])->map(fn ($c) => [
            'name'            => $c['name'],
            'color'           => $c['color'] ?? 'gray',
            'total'           => $c['amount'],
            'total_formatted' => $rp($c['amount']),
            'percentage'      => $c['pct'],
        ])->values();

        return $this->successResponse(array_merge($dashboard, [
            'summary' => [
                'total_balance'           => $totalBalance,
                'total_balance_formatted' => $rp($totalBalance),
                'cash_in_this_month'      => $cashIn,
                'cash_in_formatted'       => $rp($cashIn),
                'cash_out_this_month'     => $cashOut,
                'cash_out_formatted'      => $rp($cashOut),
                'net_cashflow'            => $cashIn - $cashOut,
                'net_cashflow_formatted'  => $rp($cashIn - $cashOut),
            ],
            'charts' => array_merge($dashboard['charts'], [
                'expense_categories'    => $legacyExpenseCategories,
                'balance_trend_30_days' => $dashboard['charts']['daily_balance_30'],
            ]),
            'recent_transactions' => $recentTransactions,
        ]), 'Data dashboard berhasil diambil.');
    }
}

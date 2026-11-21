<?php

namespace App\Http\Controllers;

use App\Models\FuturePlan;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) Auth::id();

        $totalBalance = (float) Saving::where('user_id', $userId)->sum('current_balance');

        $threeMonthsAgo = now()->subMonths(2)->startOfMonth();
        $recentTx = Transaction::where('user_id', $userId)
            ->whereNull('transfer_group_id')
            ->where('transaction_date', '>=', $threeMonthsAgo)
            ->get();

        $totalIn = (float) $recentTx->where('type', 'Cash In')->sum('amount');
        $totalOut = (float) $recentTx->where('type', 'Cash Out')->sum('amount');

        $avgMonthlyIncome = round($totalIn / 3, 0);
        $avgMonthlyExpense = round($totalOut / 3, 0);
        $suggestedMonthlySaving = max(500000.0, $avgMonthlyIncome - $avgMonthlyExpense);

        $futurePlans = FuturePlan::with(['savingAccount', 'items'])
            ->accessibleBy($userId)
            ->latest('id')
            ->get()
            ->map(function (FuturePlan $plan) {
                return [
                    'id'              => $plan->id,
                    'title'           => $plan->title,
                    'target_amount'   => (float) $plan->total_estimated_amount,
                    'remaining_amount'=> (float) $plan->remaining_amount,
                    'linked_balance'  => (float) ($plan->savingAccount?->current_balance ?? 0),
                ];
            });

        return view('simulation.index', compact(
            'totalBalance',
            'avgMonthlyIncome',
            'avgMonthlyExpense',
            'suggestedMonthlySaving',
            'futurePlans'
        ));
    }
}

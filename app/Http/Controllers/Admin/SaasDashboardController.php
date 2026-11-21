<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaasDashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth();

        $totalUsers = User::count();
        $activeProUsers = User::where('plan_tier', 'pro')
            ->where(function ($q) {
                $q->whereNull('plan_expires_at')->orWhere('plan_expires_at', '>', now());
            })
            ->count();

        $aiRequestsThisMonth = AiUsageLog::where('created_at', '>=', $monthStart)->count();
        $estimatedMrr = $activeProUsers * (float) config('saas.plans.pro.price_monthly', 39000);

        $users = User::withCount([
            'aiUsageLogs as monthly_ai_count' => fn($q) => $q->where('created_at', '>=', $monthStart),
        ])
            ->orderByDesc('id')
            ->paginate(20);

        $recentSubscriptions = Subscription::with('user:id,name,email')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.saas.index', compact(
            'totalUsers',
            'activeProUsers',
            'aiRequestsThisMonth',
            'estimatedMrr',
            'users',
            'recentSubscriptions'
        ));
    }

    public function toggleTier(Request $request, User $user): RedirectResponse
    {
        if ($user->plan_tier === 'pro') {
            $user->update([
                'plan_tier'       => 'free',
                'plan_expires_at' => null,
            ]);
            return redirect()->route('admin.saas.index')
                ->with('success', "Paket {$user->name} diubah ke Free.");
        }

        $user->update([
            'plan_tier'       => 'pro',
            'plan_expires_at' => now()->addYear(),
        ]);

        return redirect()->route('admin.saas.index')
            ->with('success', "Paket {$user->name} di-upgrade ke Finansia Pro (1 tahun).");
    }
}

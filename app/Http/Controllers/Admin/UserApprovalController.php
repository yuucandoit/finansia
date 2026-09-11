<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search');

        $query = User::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $statusFilter);
        }

        $users = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => User::count(),
            'pending' => User::where('status', 'pending')->count(),
            'approved' => User::where('status', 'approved')->count(),
            'rejected' => User::where('status', 'rejected')->count(),
        ];

        return view('admin.user-approvals.index', compact('users', 'stats', 'statusFilter', 'search'));
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update(['status' => 'approved']);

        return back()->with('success', "Akun {$user->name} ({$user->email}) berhasil disetujui! Pengguna kini dapat masuk.");
    }

    public function reject(User $user): RedirectResponse
    {
        if ($user->isMasterMind()) {
            return back()->with('error', 'Akun Master Mind tidak dapat ditolak!');
        }

        $user->update(['status' => 'rejected']);

        return back()->with('warning', "Pendaftaran akun {$user->name} ({$user->email}) telah ditolak.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isMasterMind()) {
            return back()->with('error', 'Akun Master Mind tidak dapat dihapus!');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "Akun {$userName} berhasil dihapus.");
    }
}

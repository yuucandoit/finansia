<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApprovalController extends Controller
{
    use ApiResponse;

    /**
     * Memeriksa otorisasi khusus Master Mind (wahyusnjy).
     */
    protected function authorizeMasterMind(Request $request): ?JsonResponse
    {
        if (!$request->user()->isMasterMind()) {
            return $this->errorResponse('Akses ditolak. Anda tidak memiliki izin Master Mind.', 403);
        }
        return null;
    }

    /**
     * Menampilkan daftar pengguna dan status persetujuan.
     */
    public function index(Request $request): JsonResponse
    {
        if ($deny = $this->authorizeMasterMind($request)) {
            return $deny;
        }

        $statusFilter = $request->query('status', 'all');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $query = User::query()->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $statusFilter);
        }

        $paginated = $query->paginate($perPage);

        $stats = [
            'total'    => User::count(),
            'pending'  => User::where('status', 'pending')->count(),
            'approved' => User::where('status', 'approved')->count(),
            'rejected' => User::where('status', 'rejected')->count(),
        ];

        $users = collect($paginated->items())->map(function ($u) {
            return [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'status'     => $u->status,
                'is_admin'   => $u->isMasterMind(),
                'created_at' => $u->created_at?->translatedFormat('d M Y, H:i'),
            ];
        });

        return $this->successResponse([
            'stats'      => $stats,
            'users'      => $users,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ], 'Daftar pengguna berhasil diambil.');
    }

    /**
     * Menyetujui pendaftaran pengguna baru.
     */
    public function approve(Request $request, User $user): JsonResponse
    {
        if ($deny = $this->authorizeMasterMind($request)) {
            return $deny;
        }

        $user->update(['status' => 'approved']);
        $user->ensureDefaultCashSaving();

        return $this->successResponse([
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'status' => $user->status,
        ], "Akun {$user->name} ({$user->email}) berhasil disetujui! Pengguna kini dapat masuk via aplikasi.");
    }

    /**
     * Menolak pendaftaran pengguna.
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        if ($deny = $this->authorizeMasterMind($request)) {
            return $deny;
        }

        if ($user->isMasterMind()) {
            return $this->errorResponse('Akun Master Mind tidak dapat ditolak!', 422);
        }

        $user->update(['status' => 'rejected']);

        return $this->successResponse([
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'status' => $user->status,
        ], "Pendaftaran akun {$user->name} ({$user->email}) telah ditolak.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuturePlanController extends Controller
{
    /**
     * Halaman daftar seluruh Future Plan milik pengguna dan rencana kolaborasi bersama.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $statusFilter = $request->query('status');

        $query = FuturePlan::with(['rootItems.allChildren', 'items', 'members', 'user'])
            ->accessibleBy($user->id)
            ->latest('id');

        if ($statusFilter && in_array($statusFilter, [
            FuturePlan::STATUS_PLANNED,
            FuturePlan::STATUS_IN_PROGRESS,
            FuturePlan::STATUS_COMPLETED,
            FuturePlan::STATUS_CANCELLED,
        ])) {
            $query->where('status', $statusFilter);
        }

        $plans = $query->paginate(12)->withQueryString();

        // Ringkasan KPI seluruh rencana yang dapat diakses pengguna
        $allUserPlans = FuturePlan::with(['rootItems.allChildren', 'items'])
            ->accessibleBy($user->id)
            ->get();

        $totalPlansCount = $allUserPlans->count();
        $grandTotalEstimated = (float) $allUserPlans->sum('total_estimated_amount');
        $grandTotalCompleted = (float) $allUserPlans->sum('total_completed_amount');
        $grandTotalRemaining = max(0.0, $grandTotalEstimated - $grandTotalCompleted);
        $completedPlansCount = $allUserPlans->where('status', FuturePlan::STATUS_COMPLETED)->count();

        return view('future-planner.index', compact(
            'plans',
            'totalPlansCount',
            'grandTotalEstimated',
            'grandTotalCompleted',
            'grandTotalRemaining',
            'completedPlansCount',
            'statusFilter'
        ));
    }

    /**
     * Menyimpan Future Plan baru (Master).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status'      => 'nullable|in:planned,in_progress,completed,cancelled',
            'color'       => 'nullable|string|max:30',
        ], [
            'title.required' => 'Nama rencana masa depan wajib diisi.',
            'target_date.date' => 'Format tanggal target tidak valid.',
        ]);

        $plan = FuturePlan::create([
            'user_id'     => auth()->id(),
            'title'       => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status'      => $validated['status'] ?? FuturePlan::STATUS_PLANNED,
            'color'       => $validated['color'] ?? 'indigo',
        ]);

        return redirect()
            ->route('future-planner.show', $plan)
            ->with('success', 'Rencana "' . $plan->title . '" berhasil dibuat! Silakan tambahkan daftar item kebutuhan Anda di bawah.');
    }

    /**
     * Halaman Detail Future Plan: Menampilkan ringkasan, kolaborator aktif, dan daftar item tugas bertingkat.
     */
    public function show(FuturePlan $futurePlan, Request $request): View
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat rencana ini.');

        // Update waktu aktif pengguna saat membuka halaman rencana
        $futurePlan->touchActiveUser($user);

        $filter = $request->query('filter'); // 'all', 'pending', 'completed'
        $items = FuturePlanItem::getFlatTreeForPlan($futurePlan->id, $filter);

        // Ambil opsi item induk untuk pilihan dropdown di modal tambah/edit item
        $availableParents = $futurePlan->items()
            ->orderBy('title')
            ->get(['id', 'title', 'parent_id']);

        // Kalkulasi metrik kebutuhan dana
        $totalEstimated = $futurePlan->total_estimated_amount;
        $totalCompleted = $futurePlan->total_completed_amount;
        $totalRemaining = $futurePlan->remaining_amount;
        $progressPercentage = $futurePlan->progress_percentage;

        $totalItemsCount = $futurePlan->total_items_count;
        $completedItemsCount = $futurePlan->completed_items_count;
        $totalQuantity = (int) $futurePlan->items()->sum('quantity');

        // Kolaborator & Pengguna Aktif
        $activeUsers = $futurePlan->getActiveUsers();
        $allMembers = $futurePlan->members()
            ->orderBy('future_plan_members.role', 'desc')
            ->get();
        $isOwner = $futurePlan->isOwner($user);

        return view('future-planner.show', compact(
            'futurePlan',
            'items',
            'availableParents',
            'totalEstimated',
            'totalCompleted',
            'totalRemaining',
            'progressPercentage',
            'totalItemsCount',
            'completedItemsCount',
            'totalQuantity',
            'filter',
            'activeUsers',
            'allMembers',
            'isOwner'
        ));
    }

    /**
     * Heartbeat endpoint untuk memperbarui status online user dan mengembalikan daftar user yang sedang aktif.
     */
    public function heartbeat(FuturePlan $futurePlan): JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak.');

        $futurePlan->touchActiveUser($user);

        return response()->json([
            'success'      => true,
            'active_users' => $futurePlan->getActiveUsers(),
        ]);
    }

    /**
     * Bergabung ke Future Plan menggunakan kode unik.
     */
    public function join(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ], [
            'code.required' => 'Kode rencana unik wajib dimasukkan.',
        ]);

        $rawCode = trim($validated['code']);
        if (preg_match('/FP-[A-Z0-9]+/i', $rawCode, $matches)) {
            $code = strtoupper($matches[0]);
        } else {
            $code = strtoupper($rawCode);
        }

        $plan = FuturePlan::where('share_code', $code)->first();

        if (!$plan) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode rencana "' . $code . '" tidak ditemukan. Silakan periksa kembali kode unik rencana.',
                ], 404);
            }

            return back()->with('error', 'Kode rencana "' . $code . '" tidak ditemukan. Pastikan kode unik sudah tepat.');
        }

        $user = auth()->user();
        $plan->addMemberByUser($user, 'editor');
        $plan->touchActiveUser($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Berhasil bergabung ke rencana "' . $plan->title . '"!',
                'redirect_url' => route('future-planner.show', $plan),
                'plan'         => $plan,
            ]);
        }

        return redirect()
            ->route('future-planner.show', $plan)
            ->with('success', 'Selamat datang! Anda berhasil bergabung ke rencana kolaborasi "' . $plan->title . '".');
    }

    /**
     * Direct join handler via link (misal: /future-planner/join/FP-XXXXXX).
     */
    public function showJoinForm(?string $code = null): RedirectResponse
    {
        if ($code) {
            $cleanCode = strtoupper(trim($code));
            $plan = FuturePlan::where('share_code', $cleanCode)->first();

            if ($plan) {
                $user = auth()->user();
                $plan->addMemberByUser($user, 'editor');
                $plan->touchActiveUser($user);

                return redirect()
                    ->route('future-planner.show', $plan)
                    ->with('success', 'Selamat datang! Anda berhasil bergabung ke rencana kolaborasi "' . $plan->title . '".');
            }
        }

        return redirect()
            ->route('future-planner.index')
            ->with('error', 'Kode rencana tidak valid atau tidak ditemukan.');
    }

    /**
     * Menambahkan user lain langsung ke rencana via email.
     */
    public function addMember(Request $request, FuturePlan $futurePlan): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->isOwner($user), 403, 'Hanya pemilik rencana yang berhak menambahkan anggota secara langsung.');

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => 'nullable|string|in:editor,viewer',
        ], [
            'email.required' => 'Email anggota wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.exists'   => 'Pengguna dengan email tersebut tidak terdaftar di Finansia.',
        ]);

        $targetUser = User::where('email', strtolower(trim($validated['email'])))->firstOrFail();

        $role = $validated['role'] ?? 'editor';
        $futurePlan->addMemberByUser($targetUser, $role);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna ' . $targetUser->name . ' berhasil ditambahkan sebagai anggota rencana.',
                'member'  => [
                    'id'    => $targetUser->id,
                    'name'  => $targetUser->name,
                    'email' => $targetUser->email,
                    'role'  => $role,
                ],
            ]);
        }

        return back()->with('success', 'Pengguna "' . $targetUser->name . '" berhasil ditambahkan ke dalam rencana ini.');
    }

    /**
     * Menghapus anggota dari rencana atau keluar dari rencana bersama.
     */
    public function removeMember(FuturePlan $futurePlan, User $user): RedirectResponse|JsonResponse
    {
        $currentUser = auth()->user();

        // Pemilik tidak dapat dikeluarkan
        if ($futurePlan->isOwner($user)) {
            abort(400, 'Pemilik rencana tidak dapat dihapus dari daftar anggota.');
        }

        // Hanya pemilik atau user yang bersangkutan yang boleh menghapus
        if (!$futurePlan->isOwner($currentUser) && $currentUser->id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus anggota ini.');
        }

        $futurePlan->members()->detach($user->id);

        $isSelf = $currentUser->id === $user->id;

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $isSelf ? 'Anda berhasil keluar dari rencana.' : 'Anggota berhasil dihapus dari rencana.',
            ]);
        }

        if ($isSelf) {
            return redirect()
                ->route('future-planner.index')
                ->with('success', 'Anda telah keluar dari rencana "' . $futurePlan->title . '".');
        }

        return back()->with('success', 'Anggota "' . $user->name . '" berhasil dihapus dari rencana.');
    }

    /**
     * Memperbarui informasi Master Future Plan.
     */
    public function update(Request $request, FuturePlan $futurePlan): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak.');

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status'      => 'nullable|in:planned,in_progress,completed,cancelled',
            'color'       => 'nullable|string|max:30',
        ]);

        $futurePlan->update([
            'title'       => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status'      => $validated['status'] ?? $futurePlan->status,
            'color'       => $validated['color'] ?? $futurePlan->color,
        ]);

        $futurePlan->touchActiveUser($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rencana berhasil diperbarui.',
                'data'    => $futurePlan,
            ]);
        }

        return back()->with('success', 'Rencana "' . $futurePlan->title . '" berhasil diperbarui.');
    }

    /**
     * Menghapus Future Plan beserta seluruh itemnya (Hanya Pemilik).
     */
    public function destroy(FuturePlan $futurePlan): RedirectResponse
    {
        abort_unless($futurePlan->isOwner(auth()->user()), 403, 'Hanya pemilik rencana yang berhak menghapus rencana ini.');

        $title = $futurePlan->title;
        $futurePlan->delete();

        return redirect()
            ->route('future-planner.index')
            ->with('success', 'Rencana "' . $title . '" beserta seluruh itemnya berhasil dihapus.');
    }

    /**
     * Menyimpan Item baru (atau Sub-Item) pada Future Plan.
     */
    public function storeItem(Request $request, FuturePlan $futurePlan): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak.');

        $validated = $request->validate([
            'parent_id'    => 'nullable|integer|exists:future_plan_items,id',
            'title'        => 'required|string|max:255',
            'quantity'     => 'nullable|integer|min:1',
            'unit_price'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'is_completed' => 'nullable|boolean',
        ], [
            'title.required' => 'Nama item wajib diisi.',
            'quantity.min'   => 'Jumlah minimal 1.',
            'unit_price.min' => 'Harga tidak boleh negatif.',
        ]);

        if (!empty($validated['parent_id'])) {
            $parentItem = FuturePlanItem::where('id', $validated['parent_id'])
                ->where('future_plan_id', $futurePlan->id)
                ->first();

            if (!$parentItem) {
                return back()->with('error', 'Induk item yang dipilih tidak valid.');
            }
        }

        $nextOrder = (FuturePlanItem::where('future_plan_id', $futurePlan->id)
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('order') ?? 0) + 1;

        $item = FuturePlanItem::create([
            'future_plan_id' => $futurePlan->id,
            'parent_id'      => $validated['parent_id'] ?? null,
            'title'          => trim($validated['title']),
            'quantity'       => max(1, (int) ($validated['quantity'] ?? 1)),
            'unit_price'     => (float) ($validated['unit_price'] ?? 0),
            'notes'          => $validated['notes'] ?? null,
            'is_completed'   => (bool) ($validated['is_completed'] ?? false),
            'order'          => $nextOrder,
        ]);

        $futurePlan->touchActiveUser($user);

        if ($request->wantsJson()) {
            $freshPlan = $futurePlan->fresh(['rootItems.allChildren', 'items']);
            return response()->json([
                'success'             => true,
                'message'             => 'Item berhasil ditambahkan.',
                'item'                => $item,
                'total_estimated'     => $freshPlan->total_estimated_amount,
                'total_completed'     => $freshPlan->total_completed_amount,
                'total_remaining'     => $freshPlan->remaining_amount,
                'progress_percentage' => $freshPlan->progress_percentage,
                'total_items_count'   => $freshPlan->total_items_count,
                'completed_count'     => $freshPlan->completed_items_count,
                'total_quantity'      => (int) $freshPlan->items()->sum('quantity'),
            ], 201);
        }

        return back()->with('success', 'Item "' . $item->title . '" berhasil ditambahkan.');
    }

    /**
     * Memperbarui detail Item (nama, qty, harga satuan, notes, status).
     */
    public function updateItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $validated = $request->validate([
            'parent_id'    => 'nullable|integer|exists:future_plan_items,id',
            'title'        => 'required|string|max:255',
            'quantity'     => 'nullable|integer|min:1',
            'unit_price'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'is_completed' => 'nullable|boolean',
        ]);

        // Pencegahan circular reference jika parent diubah
        $newParentId = $validated['parent_id'] ?? null;
        if (!empty($newParentId)) {
            if ($newParentId == $item->id || $this->isDescendant((int) $newParentId, $item->id)) {
                return back()->with('error', 'Item tidak dapat dijadikan anak dari dirinya sendiri atau sub-cabangnya.');
            }

            $parentItem = FuturePlanItem::where('id', $newParentId)
                ->where('future_plan_id', $futurePlan->id)
                ->first();

            if (!$parentItem) {
                return back()->with('error', 'Induk item tidak valid.');
            }
        }

        $item->update([
            'parent_id'    => $newParentId,
            'title'        => trim($validated['title']),
            'quantity'     => max(1, (int) ($validated['quantity'] ?? $item->quantity)),
            'unit_price'   => (float) ($validated['unit_price'] ?? $item->unit_price),
            'notes'        => $validated['notes'] ?? null,
            'is_completed' => array_key_exists('is_completed', $validated) ? (bool) $validated['is_completed'] : $item->is_completed,
        ]);

        $futurePlan->touchActiveUser($user);

        if ($request->wantsJson()) {
            $freshPlan = $futurePlan->fresh(['rootItems.allChildren', 'items']);
            return response()->json([
                'success'             => true,
                'message'             => 'Item berhasil diperbarui.',
                'item'                => $item,
                'total_estimated'     => $freshPlan->total_estimated_amount,
                'total_completed'     => $freshPlan->total_completed_amount,
                'total_remaining'     => $freshPlan->remaining_amount,
                'progress_percentage' => $freshPlan->progress_percentage,
                'total_items_count'   => $freshPlan->total_items_count,
                'completed_count'     => $freshPlan->completed_items_count,
                'total_quantity'      => (int) $freshPlan->items()->sum('quantity'),
            ]);
        }

        return back()->with('success', 'Item "' . $item->title . '" berhasil diperbarui.');
    }

    /**
     * Memperbarui parent_id (hierarki cabang/indent/outdent) item.
     */
    public function reparentItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:future_plan_items,id',
        ]);

        $newParentId = $validated['parent_id'] ?? null;

        if (!empty($newParentId)) {
            if ($newParentId == $item->id || $this->isDescendant((int) $newParentId, $item->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item tidak dapat dijadikan anak dari dirinya sendiri atau sub-cabangnya.',
                ], 422);
            }

            $parentItem = FuturePlanItem::where('id', $newParentId)
                ->where('future_plan_id', $futurePlan->id)
                ->first();

            if (!$parentItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Induk item tidak ditemukan.',
                ], 404);
            }
        }

        $item->update(['parent_id' => $newParentId]);
        $futurePlan->touchActiveUser($user);

        $freshPlan = $futurePlan->fresh(['rootItems.allChildren', 'items']);

        return response()->json([
            'success'             => true,
            'message'             => 'Hierarki cabang berhasil diperbarui.',
            'item'                => $item,
            'total_estimated'     => $freshPlan->total_estimated_amount,
            'total_completed'     => $freshPlan->total_completed_amount,
            'total_remaining'     => $freshPlan->remaining_amount,
            'progress_percentage' => $freshPlan->progress_percentage,
        ]);
    }

    /**
     * Toggle status centang (selesai/terbeli) pada item.
     */
    public function toggleItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $item->is_completed = !$item->is_completed;
        $item->save();

        $futurePlan->touchActiveUser($user);

        if ($request->wantsJson() || $request->ajax()) {
            $freshPlan = $futurePlan->fresh(['rootItems.allChildren', 'items']);

            return response()->json([
                'success'             => true,
                'is_completed'        => $item->is_completed,
                'total_estimated'     => $freshPlan->total_estimated_amount,
                'total_completed'     => $freshPlan->total_completed_amount,
                'total_remaining'     => $freshPlan->remaining_amount,
                'progress_percentage' => $freshPlan->progress_percentage,
                'completed_count'     => $freshPlan->completed_items_count,
            ]);
        }

        $msg = $item->is_completed ? 'Item ditandai sudah terbeli/selesai.' : 'Item ditandai belum selesai.';
        return back()->with('success', $msg);
    }

    /**
     * Menghapus Item beserta sub-item di bawahnya.
     */
    public function destroyItem(FuturePlan $futurePlan, FuturePlanItem $item): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $title = $item->title;
        $item->delete();

        $futurePlan->touchActiveUser($user);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Item "' . $title . '" beserta sub-itemnya berhasil dihapus.');
    }

    /**
     * Cek apakah candidateId adalah keturunan dari itemId (pencegahan loop hirarki).
     */
    protected function isDescendant(int $candidateId, int $itemId): bool
    {
        if ($candidateId === $itemId) {
            return true;
        }

        $cursor = FuturePlanItem::find($candidateId);
        while ($cursor && $cursor->parent_id) {
            if ($cursor->parent_id === $itemId) {
                return true;
            }
            $cursor = FuturePlanItem::find($cursor->parent_id);
        }

        return false;
    }
}

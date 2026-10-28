<?php

namespace App\Http\Controllers;

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuturePlanController extends Controller
{
    /**
     * Halaman daftar seluruh Future Plan milik pengguna.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $statusFilter = $request->query('status');

        $query = FuturePlan::with(['rootItems.allChildren', 'items'])
            ->where('user_id', $user->id)
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

        // Ringkasan KPI seluruh rencana
        $allUserPlans = FuturePlan::with(['rootItems.allChildren', 'items'])
            ->where('user_id', $user->id)
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
     * Halaman Detail Future Plan: Menampilkan ringkasan dan daftar item tugas bertingkat (Parent-Child).
     */
    public function show(FuturePlan $futurePlan, Request $request): View
    {
        abort_if($futurePlan->user_id !== auth()->id(), 403, 'Akses ditolak.');

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
            'filter'
        ));
    }

    /**
     * Memperbarui informasi Master Future Plan.
     */
    public function update(Request $request, FuturePlan $futurePlan): RedirectResponse|JsonResponse
    {
        abort_if($futurePlan->user_id !== auth()->id(), 403, 'Akses ditolak.');

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
     * Menghapus Future Plan beserta seluruh itemnya.
     */
    public function destroy(FuturePlan $futurePlan): RedirectResponse
    {
        abort_if($futurePlan->user_id !== auth()->id(), 403, 'Akses ditolak.');

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
        abort_if($futurePlan->user_id !== auth()->id(), 403, 'Akses ditolak.');

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
        abort_if($futurePlan->user_id !== auth()->id() || $item->future_plan_id !== $futurePlan->id, 403, 'Akses ditolak.');

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
        abort_if($futurePlan->user_id !== auth()->id() || $item->future_plan_id !== $futurePlan->id, 403, 'Akses ditolak.');

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
        abort_if($futurePlan->user_id !== auth()->id() || $item->future_plan_id !== $futurePlan->id, 403, 'Akses ditolak.');

        $item->is_completed = !$item->is_completed;
        $item->save();

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
        abort_if($futurePlan->user_id !== auth()->id() || $item->future_plan_id !== $futurePlan->id, 403, 'Akses ditolak.');

        $title = $item->title;
        $item->delete();

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

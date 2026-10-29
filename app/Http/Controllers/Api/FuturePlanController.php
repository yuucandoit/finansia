<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FuturePlanController extends Controller
{
    /**
     * Mengambil daftar seluruh Future Plan milik pengguna dan rencana kolaborasi bersama.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
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

        $plans = $query->get()->map(function ($plan) use ($user) {
            $isOwner = $plan->isOwner($user);
            return [
                'id'                     => $plan->id,
                'title'                  => $plan->title,
                'description'            => $plan->description,
                'target_date'            => $plan->target_date?->format('Y-m-d'),
                'status'                 => $plan->status,
                'status_label'           => $plan->status_label,
                'color'                  => $plan->color,
                'share_code'             => $plan->share_code,
                'is_owner'               => $isOwner,
                'role'                   => $isOwner ? 'owner' : 'editor',
                'total_items_count'      => $plan->total_items_count,
                'completed_items_count'  => $plan->completed_items_count,
                'total_estimated_amount' => $plan->total_estimated_amount,
                'total_completed_amount' => $plan->total_completed_amount,
                'remaining_amount'       => $plan->remaining_amount,
                'progress_percentage'    => $plan->progress_percentage,
                'created_at'             => $plan->created_at?->toIso8601String(),
                'updated_at'             => $plan->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'plans'            => $plans,
                'total_plans'      => $plans->count(),
                'total_estimated'  => (float) $plans->sum('total_estimated_amount'),
                'total_completed'  => (float) $plans->sum('total_completed_amount'),
            ],
        ]);
    }

    /**
     * Membuat Future Plan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status'      => 'nullable|in:planned,in_progress,completed,cancelled',
            'color'       => 'nullable|string|max:30',
        ]);

        $plan = FuturePlan::create([
            'user_id'     => $request->user()->id,
            'title'       => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'target_date' => $validated['target_date'] ?? null,
            'status'      => $validated['status'] ?? FuturePlan::STATUS_PLANNED,
            'color'       => $validated['color'] ?? 'indigo',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Rencana berhasil dibuat.',
            'data'    => $plan,
        ], 201);
    }

    /**
     * Menampilkan detail Future Plan beserta daftar item bertingkat, status aktif, dan kolaborator.
     */
    public function show(Request $request, FuturePlan $futurePlan): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak.');

        $futurePlan->touchActiveUser($user);

        $filter = $request->query('filter');
        $items = FuturePlanItem::getFlatTreeForPlan($futurePlan->id, $filter);

        $formattedItems = $items->map(function ($item) {
            return [
                'id'                 => $item->id,
                'parent_id'          => $item->parent_id,
                'depth'              => $item->depth ?? 0,
                'has_children'       => $item->hasChildren(),
                'title'              => $item->title,
                'quantity'           => $item->quantity,
                'unit_price'         => (float) $item->unit_price,
                'subtotal'           => (float) $item->subtotal,
                'effective_subtotal' => (float) $item->effective_subtotal,
                'is_completed'       => (bool) $item->is_completed,
                'notes'              => $item->notes,
                'order'              => $item->order,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'plan' => [
                    'id'                     => $futurePlan->id,
                    'title'                  => $futurePlan->title,
                    'description'            => $futurePlan->description,
                    'target_date'            => $futurePlan->target_date?->format('Y-m-d'),
                    'status'                 => $futurePlan->status,
                    'status_label'           => $futurePlan->status_label,
                    'color'                  => $futurePlan->color,
                    'share_code'             => $futurePlan->share_code,
                    'is_owner'               => $futurePlan->isOwner($user),
                    'total_items_count'      => $futurePlan->total_items_count,
                    'completed_items_count'  => $futurePlan->completed_items_count,
                    'total_estimated_amount' => $futurePlan->total_estimated_amount,
                    'total_completed_amount' => $futurePlan->total_completed_amount,
                    'remaining_amount'       => $futurePlan->remaining_amount,
                    'progress_percentage'    => $futurePlan->progress_percentage,
                ],
                'items'        => $formattedItems,
                'active_users' => $futurePlan->getActiveUsers(),
                'members'      => $futurePlan->members()->get()->map(fn ($m) => [
                    'id'       => $m->id,
                    'name'     => $m->name,
                    'email'    => $m->email,
                    'role'     => $m->pivot->role ?? 'editor',
                    'is_owner' => $futurePlan->isOwner($m),
                ]),
            ],
        ]);
    }

    /**
     * Heartbeat endpoint untuk mobile/API.
     */
    public function heartbeat(Request $request, FuturePlan $futurePlan): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak.');

        $futurePlan->touchActiveUser($user);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'active_users' => $futurePlan->getActiveUsers(),
            ],
        ]);
    }

    /**
     * Join rencana via kode unik di API.
     */
    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $rawCode = trim($validated['code']);
        if (preg_match('/FP-[A-Z0-9]+/i', $rawCode, $matches)) {
            $code = strtoupper($matches[0]);
        } else {
            $code = strtoupper($rawCode);
        }

        $plan = FuturePlan::where('share_code', $code)->first();

        if (!$plan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode rencana "' . $code . '" tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        $plan->addMemberByUser($user, 'editor');
        $plan->touchActiveUser($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil bergabung ke rencana "' . $plan->title . '"!',
            'data'    => [
                'plan' => $plan,
            ],
        ]);
    }

    /**
     * Menambahkan anggota via email di API.
     */
    public function addMember(Request $request, FuturePlan $futurePlan): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->isOwner($user), 403, 'Hanya pemilik yang dapat menambahkan anggota.');

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => 'nullable|string|in:editor,viewer',
        ]);

        $targetUser = User::where('email', strtolower(trim($validated['email'])))->firstOrFail();
        $role = $validated['role'] ?? 'editor';
        $futurePlan->addMemberByUser($targetUser, $role);

        return response()->json([
            'status'  => 'success',
            'message' => 'Anggota ' . $targetUser->name . ' berhasil ditambahkan.',
            'data'    => [
                'member' => [
                    'id'    => $targetUser->id,
                    'name'  => $targetUser->name,
                    'email' => $targetUser->email,
                    'role'  => $role,
                ],
            ],
        ]);
    }

    /**
     * Menghapus anggota atau keluar dari rencana di API.
     */
    public function removeMember(Request $request, FuturePlan $futurePlan, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($futurePlan->isOwner($user)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pemilik rencana tidak dapat dihapus.',
            ], 400);
        }

        if (!$futurePlan->isOwner($currentUser) && $currentUser->id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus anggota ini.');
        }

        $futurePlan->members()->detach($user->id);

        return response()->json([
            'status'  => 'success',
            'message' => $currentUser->id === $user->id ? 'Anda telah keluar dari rencana.' : 'Anggota berhasil dihapus.',
        ]);
    }

    /**
     * Memperbarui Future Plan.
     */
    public function update(Request $request, FuturePlan $futurePlan): JsonResponse
    {
        $user = $request->user();
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Rencana berhasil diperbarui.',
            'data'    => $futurePlan,
        ]);
    }

    /**
     * Menghapus Future Plan.
     */
    public function destroy(Request $request, FuturePlan $futurePlan): JsonResponse
    {
        abort_unless($futurePlan->isOwner($request->user()), 403, 'Hanya pemilik rencana yang berhak menghapus rencana ini.');

        $futurePlan->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Rencana berhasil dihapus.',
        ]);
    }

    /**
     * Menambahkan Item (atau Sub-Item) pada Future Plan.
     */
    public function storeItem(Request $request, FuturePlan $futurePlan): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user), 403, 'Akses ditolak.');

        $validated = $request->validate([
            'parent_id'    => 'nullable|integer|exists:future_plan_items,id',
            'title'        => 'required|string|max:255',
            'quantity'     => 'nullable|integer|min:1',
            'unit_price'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'is_completed' => 'nullable|boolean',
        ]);

        if (!empty($validated['parent_id'])) {
            $parentItem = FuturePlanItem::where('id', $validated['parent_id'])
                ->where('future_plan_id', $futurePlan->id)
                ->first();

            if (!$parentItem) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Induk item yang dipilih tidak valid untuk rencana ini.',
                ], 422);
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Item berhasil ditambahkan.',
            'data'    => $item,
        ], 201);
    }

    /**
     * Memperbarui detail Item.
     */
    public function updateItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $validated = $request->validate([
            'parent_id'    => 'nullable|integer|exists:future_plan_items,id',
            'title'        => 'required|string|max:255',
            'quantity'     => 'nullable|integer|min:1',
            'unit_price'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'is_completed' => 'nullable|boolean',
        ]);

        $newParentId = $validated['parent_id'] ?? null;
        if (!empty($newParentId)) {
            if ($newParentId == $item->id || $this->isDescendant((int) $newParentId, $item->id)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Item tidak dapat dijadikan anak dari dirinya sendiri atau sub-cabangnya.',
                ], 422);
            }

            $parentItem = FuturePlanItem::where('id', $newParentId)
                ->where('future_plan_id', $futurePlan->id)
                ->first();

            if (!$parentItem) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Induk item tidak valid.',
                ], 422);
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Item berhasil diperbarui.',
            'data'    => $item,
        ]);
    }

    /**
     * Memperbarui relasi induk item (reparent / indent / outdent).
     */
    public function reparentItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:future_plan_items,id',
        ]);

        $newParentId = $validated['parent_id'] ?? null;

        if (!empty($newParentId)) {
            if ($newParentId == $item->id || $this->isDescendant((int) $newParentId, $item->id)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Item tidak dapat dijadikan anak dari dirinya sendiri atau sub-cabangnya.',
                ], 422);
            }

            $parentItem = FuturePlanItem::where('id', $newParentId)
                ->where('future_plan_id', $futurePlan->id)
                ->first();

            if (!$parentItem) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Induk item tidak ditemukan.',
                ], 404);
            }
        }

        $item->update(['parent_id' => $newParentId]);
        $futurePlan->touchActiveUser($user);

        $freshPlan = $futurePlan->fresh(['rootItems.allChildren', 'items']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Hierarki cabang berhasil diperbarui.',
            'data'    => [
                'item_id'                => $item->id,
                'parent_id'              => $item->parent_id,
                'total_estimated_amount' => $freshPlan->total_estimated_amount,
                'total_completed_amount' => $freshPlan->total_completed_amount,
                'remaining_amount'       => $freshPlan->remaining_amount,
                'progress_percentage'    => $freshPlan->progress_percentage,
            ],
        ]);
    }

    /**
     * Toggle status centang (selesai/terbeli) pada Item.
     */
    public function toggleItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $item->is_completed = !$item->is_completed;
        $item->save();

        $futurePlan->touchActiveUser($user);

        $freshPlan = $futurePlan->fresh(['rootItems.allChildren', 'items']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status item berhasil diperbarui.',
            'data'    => [
                'item_id'                => $item->id,
                'is_completed'           => $item->is_completed,
                'total_estimated_amount' => $freshPlan->total_estimated_amount,
                'total_completed_amount' => $freshPlan->total_completed_amount,
                'remaining_amount'       => $freshPlan->remaining_amount,
                'progress_percentage'    => $freshPlan->progress_percentage,
                'completed_items_count'  => $freshPlan->completed_items_count,
            ],
        ]);
    }

    /**
     * Menghapus Item.
     */
    public function destroyItem(Request $request, FuturePlan $futurePlan, FuturePlanItem $item): JsonResponse
    {
        $user = $request->user();
        abort_unless($futurePlan->hasAccess($user) && $item->future_plan_id === $futurePlan->id, 403, 'Akses ditolak.');

        $item->delete();
        $futurePlan->touchActiveUser($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Item berhasil dihapus.',
        ]);
    }

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

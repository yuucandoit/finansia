<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Services\SmartRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $customCategories = Category::where('user_id', $userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $systemCategories = Category::whereNull('user_id')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $availableColors = [
            'blue'    => 'Biru',
            'emerald' => 'Hijau Emerald',
            'indigo'  => 'Indigo',
            'purple'  => 'Ungu',
            'pink'    => 'Pink',
            'amber'   => 'Kuning Amber',
            'teal'    => 'Teal',
            'orange'  => 'Oranye',
            'red'     => 'Merah',
            'gray'    => 'Abu-abu',
        ];

        $availableIcons = [
            'fa-tag'               => 'Label Umum',
            'fa-utensils'          => 'Makanan & Minuman',
            'fa-shopping-bag'      => 'Belanja',
            'fa-car'               => 'Transportasi',
            'fa-house'             => 'Rumah / Kos',
            'fa-bolt'              => 'Listrik & Tagihan',
            'fa-heartbeat'         => 'Kesehatan',
            'fa-graduation-cap'    => 'Pendidikan',
            'fa-credit-card'       => 'Cicilan / Kartu',
            'fa-hand-holding-heart'=> 'Donasi / Sosial',
            'fa-dumbbell'          => 'Olahraga / Hobi',
            'fa-wallet'            => 'Pemasukan / Gaji',
            'fa-gift'              => 'Hadiah / Bonus',
            'fa-plane'             => 'Liburan / Travel',
        ];

        $rules = CategoryRule::with(['category', 'budgetItem'])
            ->where('user_id', $userId)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        $allCategories = Category::forUser($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $activeBudget = Budget::where('user_id', $userId)
            ->where('month', (int) now()->month)
            ->where('year', (int) now()->year)
            ->first();

        $activeBudgetItems = $activeBudget
            ? BudgetItem::where('budget_id', $activeBudget->id)->orderBy('name')->get()
            : collect();

        return view('category.index', compact(
            'customCategories',
            'systemCategories',
            'availableColors',
            'availableIcons',
            'rules',
            'allCategories',
            'activeBudgetItems'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:Cash In,Cash Out',
            'color' => 'nullable|string|max:30',
            'icon'  => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'type.required' => 'Tipe kategori wajib dipilih.',
        ]);

        $name = trim($validated['name']);

        $exists = Category::forUser($userId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', "Kategori \"{$name}\" untuk tipe {$validated['type']} sudah ada.");
        }

        Category::create([
            'user_id' => $userId,
            'name'    => $name,
            'type'    => $validated['type'],
            'color'   => $validated['color'] ?? 'blue',
            'icon'    => $validated['icon'] ?? 'fa-tag',
        ]);

        return redirect()->route('category.index')->with('success', "Kategori \"{$name}\" berhasil ditambahkan!");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless((int) $category->user_id === (int) Auth::id(), 403, 'Anda tidak memiliki izin mengubah kategori sistem atau milik pengguna lain.');

        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:Cash In,Cash Out',
            'color' => 'nullable|string|max:30',
            'icon'  => 'nullable|string|max:50',
        ]);

        $name = trim($validated['name']);

        $exists = Category::forUser(Auth::id())
            ->where('id', '!=', $category->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', "Kategori \"{$name}\" untuk tipe {$validated['type']} sudah ada.");
        }

        $category->update([
            'name'  => $name,
            'type'  => $validated['type'],
            'color' => $validated['color'] ?? $category->color,
            'icon'  => $validated['icon'] ?? $category->icon,
        ]);

        return redirect()->route('category.index')->with('success', "Kategori \"{$name}\" berhasil diperbarui!");
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless((int) $category->user_id === (int) Auth::id(), 403, 'Anda tidak memiliki izin menghapus kategori sistem atau milik pengguna lain.');

        $name = $category->name;
        $category->delete();

        return redirect()->route('category.index')->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'keyword'        => 'required|string|max:255',
            'match_type'     => 'nullable|in:contains,exact',
            'category_id'    => 'required|exists:categories,id',
            'budget_item_id' => 'nullable|exists:budget_items,id',
            'priority'       => 'nullable|integer|min:0|max:100',
        ]);

        CategoryRule::create([
            'user_id'        => Auth::id(),
            'keyword'        => trim($validated['keyword']),
            'match_type'     => $validated['match_type'] ?? 'contains',
            'category_id'    => (int) $validated['category_id'],
            'budget_item_id' => !empty($validated['budget_item_id']) ? (int) $validated['budget_item_id'] : null,
            'priority'       => (int) ($validated['priority'] ?? 10),
            'is_active'      => true,
        ]);

        return redirect()->route('category.index')->with('success', 'Smart Rule berhasil ditambahkan!');
    }

    public function toggleRule(CategoryRule $rule): RedirectResponse
    {
        abort_unless((int) $rule->user_id === (int) Auth::id(), 403);

        $rule->update(['is_active' => !$rule->is_active]);

        return redirect()->route('category.index')->with('success', 'Status Smart Rule diperbarui.');
    }

    public function destroyRule(CategoryRule $rule): RedirectResponse
    {
        abort_unless((int) $rule->user_id === (int) Auth::id(), 403);

        $rule->delete();

        return redirect()->route('category.index')->with('success', 'Smart Rule berhasil dihapus.');
    }

    public function matchRule(Request $request, SmartRuleService $smartRuleService): JsonResponse
    {
        $description = (string) $request->query('description', '');
        $date = $request->query('date');

        $result = $smartRuleService->suggestForUser((int) Auth::id(), $description, $date);

        return response()->json($result);
    }
}

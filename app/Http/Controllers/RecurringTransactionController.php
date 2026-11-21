<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Saving;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecurringTransactionController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $recurrings = RecurringTransaction::with(['savingAccount.bank', 'category'])
            ->where('user_id', $userId)
            ->orderByDesc('is_active')
            ->orderBy('next_run_date')
            ->get();

        $savings = Saving::where('user_id', $userId)->with('bank')->orderBy('name')->get();
        $categories = Category::forUser($userId)->orderBy('name')->get();

        $activeMonthlyOut = (float) $recurrings->where('is_active', true)->where('type', 'Cash Out')->sum('amount');
        $activeMonthlyIn = (float) $recurrings->where('is_active', true)->where('type', 'Cash In')->sum('amount');

        return view('recurring.index', compact(
            'recurrings',
            'savings',
            'categories',
            'activeMonthlyOut',
            'activeMonthlyIn'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'description'   => 'required|string|max:255',
            'type'          => 'required|in:Cash In,Cash Out',
            'amount'        => 'required|numeric|min:1000',
            'savings_id'    => 'required|integer|exists:savings,id',
            'category_id'   => 'nullable|integer|exists:categories,id',
            'frequency'     => 'required|in:daily,weekly,monthly,yearly',
            'next_run_date' => 'required|date',
        ]);

        Saving::where('user_id', $userId)->findOrFail($validated['savings_id']);

        RecurringTransaction::create([
            'user_id'       => $userId,
            'savings_id'    => $validated['savings_id'],
            'category_id'   => $validated['category_id'] ?? null,
            'type'          => $validated['type'],
            'description'   => trim($validated['description']),
            'amount'        => (float) $validated['amount'],
            'frequency'     => $validated['frequency'],
            'next_run_date' => $validated['next_run_date'],
            'is_active'     => true,
        ]);

        return redirect()->route('recurring.index')->with('success', 'Jadwal transaksi berulang berhasil ditambahkan!');
    }

    public function toggle(RecurringTransaction $recurring): RedirectResponse
    {
        abort_unless((int) $recurring->user_id === (int) Auth::id(), 403);

        $recurring->update(['is_active' => !$recurring->is_active]);

        $msg = $recurring->is_active ? 'Jadwal transaksi rutin diaktifkan kembali.' : 'Jadwal transaksi rutin dijeda sementara.';
        return redirect()->route('recurring.index')->with('success', $msg);
    }

    public function runNow(RecurringTransaction $recurring): RedirectResponse
    {
        abort_unless((int) $recurring->user_id === (int) Auth::id(), 403);

        try {
            $recurring->executeNow(now());
            return redirect()->route('recurring.index')->with('success', "Transaksi \"{$recurring->description}\" berhasil dieksekusi & saldo rekening telah diperbarui!");
        } catch (\Exception $e) {
            return redirect()->route('recurring.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(RecurringTransaction $recurring): RedirectResponse
    {
        abort_unless((int) $recurring->user_id === (int) Auth::id(), 403);

        $recurring->delete();

        return redirect()->route('recurring.index')->with('success', 'Jadwal transaksi berulang berhasil dihapus.');
    }
}

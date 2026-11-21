<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SharedWalletController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $ownedSavings = Saving::with(['members:id,name,email', 'bank'])
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $sharedWithMe = Saving::with([
            'user:id,name,email',
            'members:id,name,email',
            'transaction' => fn($q) => $q->with('category:id,name')->latest('transaction_date')->latest('id')->limit(5),
        ])
            ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
            ->orderBy('name')
            ->get();

        $categories = Category::forUser($user->id)->orderBy('type')->orderBy('name')->get();

        return view('shared-wallet.index', compact(
            'ownedSavings',
            'sharedWithMe',
            'categories'
        ));
    }

    public function addMember(Request $request, Saving $saving): RedirectResponse
    {
        abort_unless((int) $saving->user_id === (int) Auth::id(), 403, 'Hanya pemilik rekening yang dapat mengundang anggota.');

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => 'required|in:editor,viewer',
        ], [
            'email.exists' => 'Pengguna dengan email tersebut belum terdaftar di Finansia.',
        ]);

        $member = User::where('email', $validated['email'])->firstOrFail();
        if ((int) $member->id === (int) Auth::id()) {
            return redirect()->route('shared-wallet.index')->with('error', 'Anda sudah menjadi pemilik rekening ini.');
        }

        $saving->members()->syncWithoutDetaching([
            $member->id => ['role' => $validated['role']],
        ]);

        return redirect()->route('shared-wallet.index')
            ->with('success', "{$member->name} berhasil ditambahkan ke dompet bersama \"{$saving->name}\" sebagai {$validated['role']}.");
    }

    public function removeMember(Saving $saving, User $member): RedirectResponse
    {
        $authId = (int) Auth::id();
        abort_unless(
            (int) $saving->user_id === $authId || (int) $member->id === $authId,
            403
        );

        $saving->members()->detach($member->id);

        return redirect()->route('shared-wallet.index')
            ->with('success', "Akses {$member->name} pada dompet \"{$saving->name}\" telah dicabut.");
    }

    public function storeTransaction(Request $request, Saving $saving): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($saving->canRecordBy($user), 403, 'Anda tidak memiliki izin mencatat transaksi pada dompet bersama ini.');

        $validated = $request->validate([
            'type'             => 'required|in:Cash In,Cash Out',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:1',
            'category_id'      => 'nullable|exists:categories,id',
            'transaction_date' => 'nullable|date',
        ]);

        $amount = (float) $validated['amount'];

        try {
            DB::transaction(function () use ($saving, $user, $validated, $amount) {
                $locked = Saving::lockForUpdate()->findOrFail($saving->id);

                if ($validated['type'] === 'Cash Out' && (float) $locked->current_balance < $amount) {
                    throw new \Exception("Saldo dompet bersama {$locked->name} tidak mencukupi.");
                }

                Transaction::create([
                    'user_id'          => $locked->user_id,
                    'savings_id'       => $locked->id,
                    'category_id'      => $validated['category_id'] ?? null,
                    'type'             => $validated['type'],
                    'direction'        => $validated['type'] === 'Cash In' ? Transaction::DIRECTION_IN : Transaction::DIRECTION_OUT,
                    'description'      => trim($validated['description']) . " (oleh {$user->name})",
                    'amount'           => $amount,
                    'transaction_date' => $validated['transaction_date'] ?? now()->toDateString(),
                    'is_manual'        => true,
                ]);

                if ($validated['type'] === 'Cash In') {
                    $locked->increment('current_balance', $amount);
                } else {
                    $locked->decrement('current_balance', $amount);
                }
            });

            return redirect()->route('shared-wallet.index')
                ->with('success', "Transaksi pada dompet bersama \"{$saving->name}\" berhasil dicatat!");
        } catch (\Throwable $e) {
            return redirect()->route('shared-wallet.index')->with('error', $e->getMessage());
        }
    }
}

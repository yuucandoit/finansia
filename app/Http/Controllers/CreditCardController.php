<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Models\CreditCardInstallment;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CreditCardController extends Controller
{
    public function index(): View
    {
        $userId = (int) Auth::id();

        $cards = CreditCard::with(['installments' => fn($q) => $q->orderBy('status')->orderByDesc('id')])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        $savings = Saving::where('user_id', $userId)->orderBy('name')->get();

        $totalLimit = (float) $cards->sum('credit_limit');
        $totalUsed = (float) $cards->sum(fn(CreditCard $c) => $c->total_used);
        $totalAvailable = max(0.0, $totalLimit - $totalUsed);
        $totalEstimatedStatement = (float) $cards->sum(fn(CreditCard $c) => $c->estimated_statement);

        return view('credit-card.index', compact(
            'cards',
            'savings',
            'totalLimit',
            'totalUsed',
            'totalAvailable',
            'totalEstimatedStatement'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:120',
            'provider'        => 'nullable|string|max:80',
            'last_four'       => 'nullable|string|max:4',
            'credit_limit'    => 'required|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'billing_day'     => 'required|integer|min:1|max:28',
            'due_day'         => 'required|integer|min:1|max:28',
        ]);

        CreditCard::create([
            'user_id'         => Auth::id(),
            'name'            => trim($validated['name']),
            'provider'        => !empty($validated['provider']) ? trim($validated['provider']) : null,
            'last_four'       => !empty($validated['last_four']) ? trim($validated['last_four']) : null,
            'credit_limit'    => (float) $validated['credit_limit'],
            'current_balance' => (float) ($validated['current_balance'] ?? 0),
            'billing_day'     => (int) $validated['billing_day'],
            'due_day'         => (int) $validated['due_day'],
            'is_active'       => true,
        ]);

        return redirect()->route('credit-card.index')->with('success', 'Kartu kredit / PayLater berhasil ditambahkan!');
    }

    public function updateBalance(Request $request, CreditCard $card): RedirectResponse
    {
        abort_unless((int) $card->user_id === (int) Auth::id(), 403);

        $validated = $request->validate([
            'current_balance' => 'required|numeric|min:0',
        ]);

        $card->update(['current_balance' => (float) $validated['current_balance']]);

        return redirect()->route('credit-card.index')->with('success', "Tagihan reguler {$card->name} diperbarui.");
    }

    public function storeInstallment(Request $request, CreditCard $card): RedirectResponse
    {
        abort_unless((int) $card->user_id === (int) Auth::id(), 403);

        $validated = $request->validate([
            'title'          => 'required|string|max:160',
            'total_amount'   => 'required|numeric|min:1',
            'total_months'   => 'required|integer|min:1|max:120',
            'paid_months'    => 'nullable|integer|min:0',
            'monthly_amount' => 'nullable|numeric|min:1',
            'start_date'     => 'nullable|date',
        ]);

        $totalMonths = (int) $validated['total_months'];
        $paidMonths = min($totalMonths, (int) ($validated['paid_months'] ?? 0));
        $monthlyAmount = !empty($validated['monthly_amount'])
            ? (float) $validated['monthly_amount']
            : round((float) $validated['total_amount'] / $totalMonths, 2);

        CreditCardInstallment::create([
            'credit_card_id' => $card->id,
            'user_id'        => Auth::id(),
            'title'          => trim($validated['title']),
            'total_amount'   => (float) $validated['total_amount'],
            'monthly_amount' => $monthlyAmount,
            'total_months'   => $totalMonths,
            'paid_months'    => $paidMonths,
            'start_date'     => $validated['start_date'] ?? now()->toDateString(),
            'status'         => $paidMonths >= $totalMonths ? 'paid' : 'active',
        ]);

        return redirect()->route('credit-card.index')->with('success', "Cicilan \"{$validated['title']}\" berhasil ditambahkan!");
    }

    public function payInstallment(Request $request, CreditCardInstallment $installment): RedirectResponse
    {
        abort_unless((int) $installment->user_id === (int) Auth::id(), 403);

        if ($installment->status === 'paid' || $installment->paid_months >= $installment->total_months) {
            return redirect()->route('credit-card.index')->with('error', 'Cicilan ini sudah lunas.');
        }

        $validated = $request->validate([
            'savings_id' => 'nullable|integer|exists:savings,id',
        ]);

        try {
            DB::transaction(function () use ($installment, $validated) {
                $nextMonthNumber = $installment->paid_months + 1;
                $amount = (float) $installment->monthly_amount;

                if (!empty($validated['savings_id'])) {
                    $saving = Saving::where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->findOrFail($validated['savings_id']);

                    if ((float) $saving->current_balance < $amount) {
                        throw new \Exception("Saldo rekening {$saving->name} tidak mencukupi untuk membayar cicilan ini.");
                    }

                    Transaction::create([
                        'user_id'          => Auth::id(),
                        'savings_id'       => $saving->id,
                        'type'             => 'Cash Out',
                        'direction'        => Transaction::DIRECTION_OUT,
                        'description'      => "Cicilan {$installment->title} ({$nextMonthNumber}/{$installment->total_months})",
                        'amount'           => $amount,
                        'transaction_date' => now()->toDateString(),
                        'is_manual'        => true,
                    ]);

                    $saving->decrement('current_balance', $amount);
                }

                $installment->update([
                    'paid_months' => $nextMonthNumber,
                    'status'      => $nextMonthNumber >= $installment->total_months ? 'paid' : 'active',
                ]);
            });

            return redirect()->route('credit-card.index')->with('success', "Pembayaran cicilan \"{$installment->title}\" berhasil dicatat!");
        } catch (\Throwable $e) {
            return redirect()->route('credit-card.index')->with('error', $e->getMessage());
        }
    }

    public function destroyInstallment(CreditCardInstallment $installment): RedirectResponse
    {
        abort_unless((int) $installment->user_id === (int) Auth::id(), 403);

        $installment->delete();

        return redirect()->route('credit-card.index')->with('success', 'Cicilan berhasil dihapus.');
    }

    public function destroy(CreditCard $card): RedirectResponse
    {
        abort_unless((int) $card->user_id === (int) Auth::id(), 403);

        $card->delete();

        return redirect()->route('credit-card.index')->with('success', 'Kartu kredit / PayLater berhasil dihapus.');
    }
}

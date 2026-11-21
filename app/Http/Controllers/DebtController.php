<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DebtController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $typeFilter = $request->query('type');

        $query = Debt::with(['payments.savingAccount'])
            ->where('user_id', $userId)
            ->orderByRaw("CASE WHEN status = 'paid' THEN 1 ELSE 0 END")
            ->orderBy('due_date')
            ->orderByDesc('id');

        if (in_array($typeFilter, [Debt::TYPE_PAYABLE, Debt::TYPE_RECEIVABLE], true)) {
            $query->where('type', $typeFilter);
        }

        $debts = $query->get();

        $allDebts = Debt::where('user_id', $userId)->get();
        $totalPayableRemaining = (float) $allDebts->where('type', Debt::TYPE_PAYABLE)->sum('remaining_amount');
        $totalReceivableRemaining = (float) $allDebts->where('type', Debt::TYPE_RECEIVABLE)->sum('remaining_amount');
        $overdueCount = $allDebts->filter(fn (Debt $d) => $d->is_overdue)->count();

        $savings = Saving::where('user_id', $userId)->with('bank')->orderBy('name')->get();

        return view('debt.index', compact(
            'debts',
            'typeFilter',
            'totalPayableRemaining',
            'totalReceivableRemaining',
            'overdueCount',
            'savings'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'         => 'required|in:payable,receivable',
            'person_name'  => 'required|string|max:120',
            'description'  => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:1000',
            'due_date'     => 'nullable|date',
        ], [
            'person_name.required'  => 'Nama pihak / pemberi atau peminjam wajib diisi.',
            'total_amount.required' => 'Nominal total wajib diisi.',
        ]);

        Debt::create([
            'user_id'      => Auth::id(),
            'type'         => $validated['type'],
            'person_name'  => trim($validated['person_name']),
            'description'  => !empty($validated['description']) ? trim($validated['description']) : null,
            'total_amount' => (float) $validated['total_amount'],
            'paid_amount'  => 0,
            'due_date'     => $validated['due_date'] ?? null,
            'status'       => Debt::STATUS_UNPAID,
        ]);

        return redirect()->route('debt.index')->with('success', 'Catatan hutang/piutang berhasil ditambahkan!');
    }

    public function pay(Request $request, Debt $debt): RedirectResponse
    {
        $userId = Auth::id();
        abort_unless((int) $debt->user_id === (int) $userId, 403);

        if ($debt->status === Debt::STATUS_PAID) {
            return redirect()->back()->with('error', 'Catatan ini sudah lunas sepenuhnya.');
        }

        $validated = $request->validate([
            'amount'     => 'required|numeric|min:1',
            'savings_id' => 'nullable|integer|exists:savings,id',
            'paid_at'    => 'nullable|date',
            'notes'      => 'nullable|string|max:255',
        ]);

        $payAmount = min((float) $validated['amount'], $debt->remaining_amount);
        if ($payAmount <= 0) {
            return redirect()->back()->with('error', 'Nominal pembayaran tidak valid.');
        }

        $paidAt = $validated['paid_at'] ?? now()->toDateString();

        try {
            DB::transaction(function () use ($userId, $debt, $validated, $payAmount, $paidAt) {
                $txId = null;
                $savingId = !empty($validated['savings_id']) ? (int) $validated['savings_id'] : null;

                if ($savingId) {
                    $saving = Saving::where('user_id', $userId)->lockForUpdate()->findOrFail($savingId);
                    $isPayable = $debt->type === Debt::TYPE_PAYABLE;

                    if ($isPayable && (float) $saving->current_balance < $payAmount) {
                        throw new \Exception("Saldo rekening {$saving->name} tidak mencukupi untuk membayar cicilan ini.");
                    }

                    $tx = Transaction::create([
                        'user_id'          => $userId,
                        'savings_id'       => $saving->id,
                        'type'             => $isPayable ? 'Cash Out' : 'Cash In',
                        'direction'        => $isPayable ? Transaction::DIRECTION_OUT : Transaction::DIRECTION_IN,
                        'description'      => ($isPayable ? 'Bayar Hutang ke ' : 'Terima Piutang dari ') . $debt->person_name,
                        'amount'           => $payAmount,
                        'fee'              => null,
                        'transaction_date' => $paidAt,
                        'is_manual'        => true,
                    ]);

                    if ($isPayable) {
                        $saving->decrement('current_balance', $payAmount);
                    } else {
                        $saving->increment('current_balance', $payAmount);
                    }

                    $txId = $tx->id;
                }

                DebtPayment::create([
                    'debt_id'        => $debt->id,
                    'user_id'        => $userId,
                    'savings_id'     => $savingId,
                    'transaction_id' => $txId,
                    'amount'         => $payAmount,
                    'paid_at'        => $paidAt,
                    'notes'          => !empty($validated['notes']) ? trim($validated['notes']) : null,
                ]);

                $newPaid = (float) $debt->paid_amount + $payAmount;
                $newStatus = $newPaid >= (float) $debt->total_amount ? Debt::STATUS_PAID : Debt::STATUS_PARTIAL;

                $debt->update([
                    'paid_amount' => $newPaid,
                    'status'      => $newStatus,
                ]);
            });

            return redirect()->route('debt.index')->with('success', 'Pembayaran cicilan berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        abort_unless((int) $debt->user_id === (int) Auth::id(), 403);

        $debt->delete();

        return redirect()->route('debt.index')->with('success', 'Catatan hutang/piutang berhasil dihapus.');
    }
}

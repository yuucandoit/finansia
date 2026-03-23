<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $savings = Saving::where('user_id', Auth::id())
            ->with('bank')
            ->orderBy('created_at', 'DESC')
            ->get();
        
        $totalBalance = $savings->sum('current_balance');
        $banks = Bank::orderBy('name')->get();
        
        return view('saving', compact('savings', 'totalBalance', 'banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'current_balance' => 'required|numeric|min:0',
        ]);

        Saving::create([
            'user_id' => Auth::id(),
            'bank_id' => $validated['bank_id'],
            'name' => $validated['name'],
            'account_number' => $validated['account_number'],
            'current_balance' => $validated['current_balance'] ? $validated['current_balance'] : 0,
        ]);

        return redirect()->route('saving.index')->with('success', 'Tabungan berhasil ditambahkan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $saving = Saving::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'current_balance' => 'required|numeric|min:0',
        ]);

        $saving->update($validated);

        return redirect()->route('saving.index')->with('success', 'Tabungan berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $saving = Saving::where('user_id', Auth::id())->findOrFail($id);
        $saving->delete();

        return redirect()->route('saving.index')->with('success', 'Tabungan berhasil dihapus!');
    }

    public function detail(Request $request, $id)
    {
        $year = $request->year ?? date('Y');
        // dd($transaction);
        $saving = Saving::where('user_id', Auth::id())
            ->find($id);
        $transaction_query = $saving->transaction();
        $years = $saving->transaction()
            ->selectRaw('EXTRACT(YEAR FROM transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // 2. Hitung Income Tahunan
        $yearly_income = (clone $transaction_query)
            ->where('type', 'Cash In')
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        // 3. Hitung Spending Tahunan
        $yearly_spending = (clone $transaction_query)
            ->where('type', 'Cash Out')
            ->whereYear('transaction_date', $year)
            ->sum('amount');

        $income_vs_spending = (clone $transaction_query)
            ->selectRaw("EXTRACT(MONTH FROM transaction_date) as month, 
                    SUM(CASE WHEN type = 'Cash In' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN type = 'Cash Out' THEN amount ELSE 0 END) as spending")
            ->whereYear('transaction_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('saving_detail', compact('saving','yearly_income','yearly_spending','income_vs_spending','years', 'year'));
    }

    public function getMonthsApi(Request $request, $id)
    {
        $year = $request->year ?? date('Y');
        
        $months = Transaction::where('savings_id', $id)
            ->whereYear('transaction_date', $year)
            ->selectRaw("EXTRACT(MONTH FROM transaction_date) as month_num, 
                        TO_CHAR(transaction_date, 'Month') as month_name,
                        COUNT(*) as total_count")
            ->groupBy('month_num', 'month_name')
            ->orderBy('month_num', 'desc')
            ->get();

        return response()->json($months);
    }

    public function getTransactionApi(Request $request, $id)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month;
        // Validasi
        if(empty($year)){
            return response()->json([
                'status' => 'error',
                'message' => 'Year are required'
            ]);
        }elseif(empty($month)){
            return response()->json([
                'status' => 'error',
                'message' => 'Month are required'
            ]);
        }
        

        $transactions = Transaction::where('savings_id',$id)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->orderBy('transaction_date', 'DESC')
            ->get() // Kita ambil semua untuk grouping (atau gunakan pagination jika data ribuan)
            ->groupBy(function($date) {
                return \Carbon\Carbon::parse($date->transaction_date)->format('F'); // Group by "January", "February"
            });

        return response()->json($transactions);
    }
    
}

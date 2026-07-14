<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Transaction;
use App\Models\Transaction_Item;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $query = Transaction::with(['category', 'savingAccount'])->where('user_id', $userId);

        // Filter Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        // Filter Type
        if ($request->has('type') && $request->type != '') {
            $type = $request->type;
            if ($type == 'Cash Out') {
                // Opsional: Jika Pengeluaran mencakup Transfer juga
                $query->whereIn('type', ['Cash Out', 'Transfer']);
            } else {
                $query->where('type', $type);
            }
        }

        $transaction = $query->orderBy('transaction_date','DESC')->paginate(15);
        
        // Append query parameters to pagination links
        $transaction->appends($request->all());

        $currentPageItems = $transaction->getCollection();

        $totalCashIn = $currentPageItems->where('type', 'Cash In')->sum('amount');
        
        $totalCashOut = $currentPageItems->whereIn('type', ['Cash Out', 'Transfer', 'Tabungan'])
            ->sum('amount');

        $saving = Saving::where('user_id', $userId)->get();
        $categories = Category::orderBy('name')->get();
        
        return view('transaction', [
            'transaction'  => $transaction,
            'saving'       => $saving,
            'categories'   => $categories,
            'totalCashIn'  => $totalCashIn,
            'totalCashOut' => $totalCashOut,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $saving = Saving::where('user_id',Auth::user()->id)->get();
        return view('transaksi.create')
        ->with('saving',$saving);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'type' => 'required|in:Cash In,Cash Out,Transfer,Tabungan',
            'amount' => 'required|numeric|min:0',
            'savings_id' => 'required|exists:savings,id',
            'destination_savings_id' => 'nullable|exists:savings,id',
            'category_id' => 'nullable|exists:categories,id',
            'fee' => 'nullable|numeric|min:0',
            'transaction_date' => 'nullable|date',
        ]);

        $userId = auth()->id();
        $date = $request->transaction_date ?? now();
        $amount = (float) $request->amount;
        $fee = (float) ($request->fee ?? 0);
        $categoryId = $request->category_id;

        try {
            DB::transaction(function () use ($userId, $request, $date, $amount, $fee, $categoryId) {
                if ($request->type === 'Transfer' || ($request->type === 'Tabungan' && $request->destination_savings_id)) {
                    $sourceSaving = Saving::where('user_id', $userId)->findOrFail($request->savings_id);
                    $destSaving = Saving::where('user_id', $userId)->findOrFail($request->destination_savings_id);

                    // 1. Transaction Out on Source Account
                    Transaction::create([
                        'user_id' => $userId,
                        'savings_id' => $sourceSaving->id,
                        'category_id' => $categoryId,
                        'type' => $request->type,
                        'description' => $request->description . ' (ke ' . $destSaving->name . ')',
                        'amount' => $amount,
                        'fee' => $fee > 0 ? $fee : null,
                        'transaction_date' => $date,
                        'is_manual' => true,
                    ]);

                    $sourceSaving->decrement('current_balance', $amount + $fee);

                    // 2. Transaction In on Destination Account
                    Transaction::create([
                        'user_id' => $userId,
                        'savings_id' => $destSaving->id,
                        'category_id' => $categoryId,
                        'type' => $request->type,
                        'description' => $request->description . ' (dari ' . $sourceSaving->name . ')',
                        'amount' => $amount,
                        'fee' => null,
                        'transaction_date' => $date,
                        'is_manual' => true,
                    ]);

                    $destSaving->increment('current_balance', $amount);

                } else {
                    $saving = Saving::where('user_id', $userId)->findOrFail($request->savings_id);

                    Transaction::create([
                        'user_id' => $userId,
                        'savings_id' => $saving->id,
                        'category_id' => $categoryId,
                        'type' => $request->type,
                        'description' => $request->description,
                        'amount' => $amount,
                        'fee' => $fee > 0 ? $fee : null,
                        'transaction_date' => $date,
                        'is_manual' => true,
                    ]);

                    if ($request->type === 'Cash In') {
                        $saving->increment('current_balance', $amount);
                    } else {
                        // Cash Out or Tabungan without destination
                        $saving->decrement('current_balance', $amount + $fee);
                    }
                }
            });

            return redirect()->route('transaction.index')->with('success', 'Transaksi berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->route('transaction.index')->with('error', 'Gagal menambahkan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $saving = Saving::where('user_id',Auth::user()->id)->get();
        return view('pages.transaksi.edit')
        ->with('saving',$saving);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $transaksi = Transaction::find($id);

        $query = [];
        if($request->type == 'Cash In'){
            $query= [
                'save_id' => $request->save_id,
                'business_id' => $request->business_id,
                'type' => $request->type,
                'description' => $request->description,
                'fee' => $request->fee,
            ];

            $savings = Saving::find($request->save_id);
            //Jika Ganti CashIn
            if($savings->saldo < $transaksi->fee){
                $returnPlus = $transaksi->fee - $savings->saldo;
            }else if($savings->saldo > $transaksi->fee){
                $returnPlus = $savings->saldo - $transaksi->fee;
            }

            Saving::where('id',$transaksi->save_id)->update([
                'saldo' => $returnPlus
            ]);

            $plus = $savings->saldo + $request->fee;
            Saving::where('id',$request->save_id)->update([
                'saldo' => $plus
            ]);
        }else if($request->type == 'Cash Out') {
            $query= [
                'save_id' => $request->save_id,
                'business_id' => $request->business_id,
                'type' => $request->type,
                'description' => $request->description,
                'fee' => $request->fee,
            ];

            $savings = Saving::find($request->save_id);

            if($savings->saldo < $transaksi->fee){
                $returnMinus = $transaksi->fee + $savings->saldo;
            }else if($savings->saldo > $transaksi->fee){
                $returnMinus = $savings->saldo + $transaksi->fee;
            }

            Saving::where('id',$transaksi->save_id)->update([
                'saldo' => $returnMinus
            ]);

            if($savings->saldo > $request->fee){
                $minus = $savings->saldo - $request->fee;
            }else {
                $minus =  $request->fee - $savings->saldo;
            }
            Saving::where('id',$request->save_id)->update([
                'saldo' => $minus
            ]);
        }

        $transaksi->update($query);

        return redirect()->back()->with('message','Success Simpan Transaksi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }


    public function import(Request $request)
    {
        \Log::info('Import started', [
            'has_file' => $request->hasFile('file'),
            'savings_id' => $request->savings_id,
            'has_password' => !empty($request->password),
        ]);


        // Manual validation for file extension
        if (!$request->hasFile('file')) {
            return redirect()->route('transaction.index')
                ->with('error', 'File tidak ditemukan. Silakan pilih file terlebih dahulu.');
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, ['xlsx', 'xls'])) {
            \Log::error('Invalid file extension', [
                'extension' => $extension,
                'original_name' => $file->getClientOriginalName()
            ]);
            return redirect()->route('transaction.index')
                ->with('error', 'File harus berformat .xlsx atau .xls. File Anda: .' . $extension);
        }

        // Validate other fields
        $request->validate([
            'savings_id' => 'required|exists:savings,id',
            'password' => 'required|string',
        ]);

        \Log::info('Validation passed', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        try {
            // Get saving & bank info
            $saving = Saving::with('bank')->findOrFail($request->savings_id);
            
            // Determine bank type
            $bankName = strtolower($saving->bank->name ?? '');
            $bankType = 'mandiri'; // Default
            
            if (str_contains($bankName, 'mandiri')) {
                $bankType = 'mandiri';
            } elseif (str_contains($bankName, 'bca')) {
                $bankType = 'bca';
            } elseif (str_contains($bankName, 'bri')) {
                $bankType = 'bri';
            }

            // Save uploaded file temporarily
            $file = $request->file('file');
            $filePath = $file->storeAs('temp', 'import_' . time() . '.xlsx', 'public');
            $fullPath = storage_path('app/public/' . $filePath);

            // Prepare Python command using virtual environment
            $venvPath = base_path('decoder/.venv/bin/python3');
            $scriptPath = base_path('decoder/decoder.py');
            $password = escapeshellarg($request->password);
            $filePathEscaped = escapeshellarg($fullPath);
            
            $command = "{$venvPath} {$scriptPath} {$bankType} {$filePathEscaped} {$password} 2>&1";
            
            \Log::info('Executing Python decoder', [
                'command' => $command,
                'bank_type' => $bankType,
            ]);
            
            // Execute Python script
            exec($command, $output, $returnCode);
            
            \Log::info('Python execution completed', [
                'return_code' => $returnCode,
                'output_lines' => count($output),
            ]);
            
            // Clean up temporary file
            \Storage::disk('public')->delete($filePath);

            if ($returnCode !== 0) {
                $errorMsg = implode("\n", $output);
                \Log::error('Python decoder error:', ['output' => $errorMsg]);
                return redirect()->route('transaction.index')
                    ->with('error', 'Gagal memproses file: ' . (str_contains($errorMsg, 'password') ? 'Password salah' : 'Format file tidak sesuai'));
            }

            // Parse JSON output (filter out warnings)
            $jsonOutput = '';
            foreach ($output as $line) {
                // Only take lines that start with [ (JSON array)
                if (trim($line) && $line[0] === '[') {
                    $jsonOutput = $line;
                    break;
                }
            }

            if (empty($jsonOutput)) {
                \Log::error('No JSON output from decoder', ['output' => $output]);
                return redirect()->route('transaction.index')
                    ->with('error', 'Gagal mendapatkan data dari file. Cek format file atau password.');
            }

            $transactions = json_decode($jsonOutput, true);

            if (!$transactions || !is_array($transactions)) {
                \Log::error('JSON decode failed', ['json' => $jsonOutput]);
                return redirect()->route('transaction.index')
                    ->with('error', 'Gagal parsing data dari file');
            }

            // Import transactions
            $importedCount = 0;
            \Log::info('Starting transaction import', ['total' => count($transactions)]);
            
            foreach ($transactions as $trans) {
                // Determine type based on amount_in/amount_out
                $type = 'Cash Out';
                $amount = $trans['amount_out'] ?? 0;
                $lastTransaction = end($transactions);
                $latestBalance = $lastTransaction['balance'] ?? null;
                $latestTransactionDate = \Carbon\Carbon::createFromFormat('d M Y', $lastTransaction['date']);
                $isSameMonth = $latestTransactionDate->isCurrentMonth();
                $isSameYear = $latestTransactionDate->isCurrentYear();
                
                if (($trans['amount_in'] ?? 0) > 0) {
                    $type = 'Cash In';
                    $amount = $trans['amount_in'];
                }

                // Parse date (format: "02 Jan 2026" to proper date)
                $dateStr = $trans['date'] ?? date('d M Y');
                try {
                    $transactionDate = \Carbon\Carbon::createFromFormat('d M Y', $dateStr);
                } catch (\Exception $e) {
                    $transactionDate = now();
                }

                Transaction::updateOrCreate(
                    [
                        // 1. Array pertama: Kolom untuk "pencarian" (Uniqueness)
                        'user_id' => auth()->id(),
                        'savings_id' => $saving->id,
                        'transaction_date' => $transactionDate,
                        'amount' => $amount,
                        'description' => $trans['description'] ?? 'Import dari e-statement',
                    ],
                    [
                        // 2. Array kedua: Kolom yang mau di-update/set
                        'type' => $type,
                        'is_manual' => false,
                    ]
                );
                if ($latestBalance !== null && ($isSameMonth && $isSameYear)) {
                    Saving::where('id', $saving->id)->update([
                        'current_balance' => $latestBalance
                    ]);
                    
                    \Log::info('Saving balance updated', [
                        'saving_id' => $saving->id,
                        'new_balance' => $latestBalance
                    ]);
                }
                $importedCount++;
            }

            \Log::info('Import completed successfully', ['imported' => $importedCount]);

            return redirect()->route('transaction.index')
                ->with('success', "Berhasil import {$importedCount} transaksi dari {$saving->bank->name}!");

        } catch (\Exception $e) {
            \Log::error('Import error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('transaction.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function import_encrypted(Request $request)
    {
        // Get the uploaded file
        $file = $request->file('excel_file');

        // Determine the password
        $password = $request->input('password'); 

        try {
            // Use the toCollection() method to directly import and get the data
            $collection = Excel::toCollection(new TransactionProtectedImport, $file, null, \Maatwebsite\Excel\Excel::XLS, [
                'password' => $password
            ]);
            
            // Return the first collection of data from the import
            return response()->json([
                'status' => 'success',
                'message' => 'File berhasil diimport.',
                'data' => $collection->first() // Get the first sheet's data
            ]);

        } catch (EncryptedFileException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata sandi file Excel salah.'
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}

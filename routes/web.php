<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FuturePlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\Admin\UserApprovalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('transaksi')->group(function () {
    Route::get('/',[TransactionController::class,'index'])->name('transaction.index')->middleware(['auth', 'verified']);
    Route::post('/store',[TransactionController::class,'store'])->name('transaction.store');
    Route::post('/import',[TransactionController::class,'import'])->name('transaction.import');
    Route::post('/import-pdf', [TransactionController::class, 'importPdfPreview'])->name('transaction.import_pdf');
    Route::post('/import-pdf/save', [TransactionController::class, 'importPdfSave'])->name('transaction.import_pdf_save');
})->middleware(['auth', 'verified']);

Route::prefix('saving')->group(function () {
    Route::get('/',[SavingController::class,'index'])->name('saving.index')->middleware(['auth', 'verified']);
    Route::post('/store',[SavingController::class,'store'])->name('saving.store');
    Route::put('/{id}',[SavingController::class,'update'])->name('saving.update');
    Route::delete('/{id}',[SavingController::class,'destroy'])->name('saving.destroy');
    Route::get('/detail/{id}',[SavingController::class,'detail'])->name('saving.detail');
})->middleware(['auth', 'verified']);

Route::prefix('anggaran')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [BudgetController::class, 'index'])->name('budget.index');
    Route::post('/store', [BudgetController::class, 'store'])->name('budget.store');
    Route::post('/copy-previous', [BudgetController::class, 'copyPrevious'])->name('budget.copy_previous');
    Route::get('/{id}/transactions', [BudgetController::class, 'transactions'])->name('budget.transactions');
    Route::put('/{budget}', [BudgetController::class, 'update'])->name('budget.update');
    Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('budget.destroy');
});

Route::prefix('future-planner')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [FuturePlanController::class, 'index'])->name('future-planner.index');
    Route::post('/store', [FuturePlanController::class, 'store'])->name('future-planner.store');
    Route::get('/{futurePlan}', [FuturePlanController::class, 'show'])->name('future-planner.show');
    Route::put('/{futurePlan}', [FuturePlanController::class, 'update'])->name('future-planner.update');
    Route::delete('/{futurePlan}', [FuturePlanController::class, 'destroy'])->name('future-planner.destroy');

    Route::post('/{futurePlan}/items', [FuturePlanController::class, 'storeItem'])->name('future-planner.items.store');
    Route::put('/{futurePlan}/items/{item}', [FuturePlanController::class, 'updateItem'])->name('future-planner.items.update');
    Route::patch('/{futurePlan}/items/{item}/reparent', [FuturePlanController::class, 'reparentItem'])->name('future-planner.items.reparent');
    Route::patch('/{futurePlan}/items/{item}/toggle', [FuturePlanController::class, 'toggleItem'])->name('future-planner.items.toggle');
    Route::delete('/{futurePlan}/items/{item}', [FuturePlanController::class, 'destroyItem'])->name('future-planner.items.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('ai-chat')->group(function () {
    Route::post('/parse', [AiChatController::class, 'parse'])->name('ai.parse');
    Route::post('/save', [AiChatController::class, 'save'])->name('ai.save');
    Route::post('/save-budget', [AiChatController::class, 'saveBudget'])->name('ai.save_budget');
});

Route::middleware(['auth', 'verified', 'master_mind'])->prefix('admin/user-approvals')->name('admin.approvals.')->group(function () {
    Route::get('/', [UserApprovalController::class, 'index'])->name('index');
    Route::post('/{user}/approve', [UserApprovalController::class, 'approve'])->name('approve');
    Route::post('/{user}/reject', [UserApprovalController::class, 'reject'])->name('reject');
    Route::delete('/{user}', [UserApprovalController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/auth.php';

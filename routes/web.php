<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FuturePlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\Admin\UserApprovalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('transaksi')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/',[TransactionController::class,'index'])->name('transaction.index');
    Route::post('/store',[TransactionController::class,'store'])->name('transaction.store');
    Route::put('/{transaction}',[TransactionController::class,'update'])->name('transaction.update');
    Route::delete('/{transaction}',[TransactionController::class,'destroy'])->name('transaction.destroy');
    Route::post('/import',[TransactionController::class,'import'])->name('transaction.import');
    Route::post('/import-pdf', [TransactionController::class, 'importPdfPreview'])->name('transaction.import_pdf');
    Route::post('/import-pdf/save', [TransactionController::class, 'importPdfSave'])->name('transaction.import_pdf_save');
});

Route::prefix('saving')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/',[SavingController::class,'index'])->name('saving.index');
    Route::post('/store',[SavingController::class,'store'])->name('saving.store');
    Route::post('/{id}/reconcile',[SavingController::class,'reconcile'])->name('saving.reconcile');
    Route::put('/{id}',[SavingController::class,'update'])->name('saving.update');
    Route::delete('/{id}',[SavingController::class,'destroy'])->name('saving.destroy');
    Route::get('/detail/{id}',[SavingController::class,'detail'])->name('saving.detail');
});

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
    Route::post('/join', [FuturePlanController::class, 'join'])->name('future-planner.join');
    Route::get('/join/{code?}', [FuturePlanController::class, 'showJoinForm'])->name('future-planner.join.form');
    Route::get('/export-template', [FuturePlanController::class, 'exportTemplate'])->name('future-planner.export-template');

    Route::get('/{futurePlan}', [FuturePlanController::class, 'show'])->name('future-planner.show');
    Route::put('/{futurePlan}', [FuturePlanController::class, 'update'])->name('future-planner.update');
    Route::patch('/{futurePlan}/saving', [FuturePlanController::class, 'linkSaving'])->name('future-planner.saving.link');
    Route::delete('/{futurePlan}', [FuturePlanController::class, 'destroy'])->name('future-planner.destroy');

    Route::get('/{futurePlan}/export', [FuturePlanController::class, 'export'])->name('future-planner.export');
    Route::post('/{futurePlan}/import', [FuturePlanController::class, 'import'])->name('future-planner.import');

    Route::post('/{futurePlan}/heartbeat', [FuturePlanController::class, 'heartbeat'])->name('future-planner.heartbeat');
    Route::post('/{futurePlan}/members', [FuturePlanController::class, 'addMember'])->name('future-planner.members.store');
    Route::delete('/{futurePlan}/members/{user}', [FuturePlanController::class, 'removeMember'])->name('future-planner.members.destroy');
    Route::post('/{futurePlan}/contributions', [FuturePlanController::class, 'storeContribution'])->name('future-planner.contributions.store');
    Route::delete('/{futurePlan}/contributions/{contribution}', [FuturePlanController::class, 'destroyContribution'])->name('future-planner.contributions.destroy');

    Route::post('/{futurePlan}/items', [FuturePlanController::class, 'storeItem'])->name('future-planner.items.store');
    Route::put('/{futurePlan}/items/{item}', [FuturePlanController::class, 'updateItem'])->name('future-planner.items.update');
    Route::patch('/{futurePlan}/items/{item}/reparent', [FuturePlanController::class, 'reparentItem'])->name('future-planner.items.reparent');
    Route::patch('/{futurePlan}/items/{item}/toggle', [FuturePlanController::class, 'toggleItem'])->name('future-planner.items.toggle');
    Route::patch('/{futurePlan}/items/{item}/assign', [FuturePlanController::class, 'assignItem'])->name('future-planner.items.assign');
    Route::post('/{futurePlan}/items/{item}/checkout', [FuturePlanController::class, 'checkoutItem'])->name('future-planner.items.checkout');
    Route::patch('/{futurePlan}/items/{item}/dates', [FuturePlanController::class, 'updateItemDates'])->name('future-planner.items.dates');
    Route::delete('/{futurePlan}/items/{item}', [FuturePlanController::class, 'destroyItem'])->name('future-planner.items.destroy');
});

Route::prefix('kategori')->middleware(['auth', 'verified'])->name('category.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::post('/store', [CategoryController::class, 'store'])->name('store');

    Route::get('/rules/match', [CategoryController::class, 'matchRule'])->name('rules.match');
    Route::post('/rules', [CategoryController::class, 'storeRule'])->name('rules.store');
    Route::patch('/rules/{rule}/toggle', [CategoryController::class, 'toggleRule'])->name('rules.toggle');
    Route::delete('/rules/{rule}', [CategoryController::class, 'destroyRule'])->name('rules.destroy');

    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

Route::prefix('transaksi-rutin')->middleware(['auth', 'verified'])->name('recurring.')->group(function () {
    Route::get('/', [RecurringTransactionController::class, 'index'])->name('index');
    Route::post('/store', [RecurringTransactionController::class, 'store'])->name('store');
    Route::patch('/{recurring}/toggle', [RecurringTransactionController::class, 'toggle'])->name('toggle');
    Route::post('/{recurring}/run', [RecurringTransactionController::class, 'runNow'])->name('run');
    Route::delete('/{recurring}', [RecurringTransactionController::class, 'destroy'])->name('destroy');
});

Route::prefix('hutang-piutang')->middleware(['auth', 'verified'])->name('debt.')->group(function () {
    Route::get('/', [DebtController::class, 'index'])->name('index');
    Route::post('/store', [DebtController::class, 'store'])->name('store');
    Route::post('/{debt}/pay', [DebtController::class, 'pay'])->name('pay');
    Route::delete('/{debt}', [DebtController::class, 'destroy'])->name('destroy');
});

Route::prefix('aset')->middleware(['auth', 'verified'])->name('asset.')->group(function () {
    Route::get('/', [AssetController::class, 'index'])->name('index');
    Route::post('/store', [AssetController::class, 'store'])->name('store');
    Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
    Route::patch('/{asset}/price', [AssetController::class, 'updatePrice'])->name('price');
    Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
});

Route::prefix('laporan')->middleware(['auth', 'verified'])->name('report.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export_excel');
    Route::get('/print-pdf', [ReportController::class, 'printPdf'])->name('print_pdf');
});

Route::prefix('dompet-bersama')->middleware(['auth', 'verified'])->name('shared-wallet.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SharedWalletController::class, 'index'])->name('index');
    Route::post('/{saving}/members', [\App\Http\Controllers\SharedWalletController::class, 'addMember'])->name('members.store');
    Route::delete('/{saving}/members/{member}', [\App\Http\Controllers\SharedWalletController::class, 'removeMember'])->name('members.destroy');
    Route::post('/{saving}/transactions', [\App\Http\Controllers\SharedWalletController::class, 'storeTransaction'])->name('transactions.store');
});

Route::prefix('kartu-kredit')->middleware(['auth', 'verified'])->name('credit-card.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CreditCardController::class, 'index'])->name('index');
    Route::post('/store', [\App\Http\Controllers\CreditCardController::class, 'store'])->name('store');
    Route::patch('/{card}/balance', [\App\Http\Controllers\CreditCardController::class, 'updateBalance'])->name('balance');
    Route::post('/{card}/installments', [\App\Http\Controllers\CreditCardController::class, 'storeInstallment'])->name('installments.store');
    Route::post('/installments/{installment}/pay', [\App\Http\Controllers\CreditCardController::class, 'payInstallment'])->name('installments.pay');
    Route::delete('/installments/{installment}', [\App\Http\Controllers\CreditCardController::class, 'destroyInstallment'])->name('installments.destroy');
    Route::delete('/{card}', [\App\Http\Controllers\CreditCardController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pricing', [\App\Http\Controllers\BillingController::class, 'pricing'])->name('pricing.index');
    Route::post('/billing/checkout', [\App\Http\Controllers\BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/simulasi', [\App\Http\Controllers\SimulationController::class, 'index'])->name('simulation.index');
    Route::get('/backup/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('profile.backup');
    Route::post('/backup/preview', [\App\Http\Controllers\BackupController::class, 'previewRestore'])->name('profile.backup.preview');
    Route::post('/backup/restore', [\App\Http\Controllers\BackupController::class, 'executeRestore'])->name('profile.backup.restore');
    Route::post('/profile/telegram/code', [\App\Http\Controllers\TelegramController::class, 'generateLinkCode'])->name('profile.telegram.code');
    Route::patch('/profile/telegram/toggle', [\App\Http\Controllers\TelegramController::class, 'toggleNotifications'])->name('profile.telegram.toggle');
    Route::delete('/profile/telegram/unlink', [\App\Http\Controllers\TelegramController::class, 'unlink'])->name('profile.telegram.unlink');
    Route::patch('/profile/payday', [\App\Http\Controllers\PaydayController::class, 'update'])->name('profile.payday.update');
});

Route::get('/notifikasi/feed', [NotificationController::class, 'feed'])
    ->middleware(['auth', 'verified'])
    ->name('notifications.feed');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('ai-chat')->group(function () {
    Route::post('/parse', [AiChatController::class, 'parse'])->name('ai.parse');
    Route::post('/parse-image', [AiChatController::class, 'parseImage'])->name('ai.parse_image');
    Route::post('/save', [AiChatController::class, 'save'])->name('ai.save');
    Route::post('/save-budget', [AiChatController::class, 'saveBudget'])->name('ai.save_budget');
    Route::post('/save-future-plan', [AiChatController::class, 'saveFuturePlan'])->name('ai.save_future_plan');
});

Route::middleware(['auth', 'verified', 'master_mind'])->prefix('admin/user-approvals')->name('admin.approvals.')->group(function () {
    Route::get('/', [UserApprovalController::class, 'index'])->name('index');
    Route::post('/{user}/approve', [UserApprovalController::class, 'approve'])->name('approve');
    Route::post('/{user}/reject', [UserApprovalController::class, 'reject'])->name('reject');
    Route::delete('/{user}', [UserApprovalController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'verified', 'master_mind'])->prefix('admin/saas')->name('admin.saas.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\SaasDashboardController::class, 'index'])->name('index');
    Route::patch('/users/{user}/tier', [\App\Http\Controllers\Admin\SaasDashboardController::class, 'toggleTier'])->name('users.toggle-tier');
});

require __DIR__.'/auth.php';

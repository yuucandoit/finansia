<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SavingController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\FuturePlanController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\UserApprovalController;

/*
|--------------------------------------------------------------------------
| Finansia REST API Routes (Version 1)
|--------------------------------------------------------------------------
|
| Rute API terstruktur dan terlindungi oleh token Laravel Sanctum.
| Didesain untuk mobile apps (Kotlin Multiplatform / Flutter).
|
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // 1. Public Authentication Routes (Tanpa Token)
    // =========================================================================
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // =========================================================================
    // 2. Protected Routes (Wajib Header: Authorization: Bearer <Token>)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {

        // --- Autentikasi & Profil Pengguna ---
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

        // --- Dashboard & Grafik ---
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // --- Rekening Tabungan & Dompet ---
        Route::get('/savings', [SavingController::class, 'index']);
        Route::post('/savings', [SavingController::class, 'store']);
        Route::get('/savings/{id}', [SavingController::class, 'show']);
        Route::put('/savings/{id}', [SavingController::class, 'update']);
        Route::delete('/savings/{id}', [SavingController::class, 'destroy']);
        Route::get('/banks', [SavingController::class, 'banks']);

        // --- Transaksi & Kategori ---
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::post('/transactions/import-pdf', [TransactionController::class, 'importPdfPreview']);
        Route::post('/transactions/import-pdf/save', [TransactionController::class, 'importPdfSave']);
        Route::get('/transactions/{id}', [TransactionController::class, 'show']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
        Route::get('/categories', [TransactionController::class, 'categories']);

        // --- Anggaran Bulanan (Budgets) ---
        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::post('/budgets', [BudgetController::class, 'store']);
        Route::post('/budgets/batch', [BudgetController::class, 'batchStore']);
        Route::post('/budgets/copy-previous', [BudgetController::class, 'copyPrevious']);
        Route::get('/budgets/{id}', [BudgetController::class, 'show']);
        Route::get('/budgets/{id}/transactions', [BudgetController::class, 'transactions']);
        Route::put('/budgets/{id}', [BudgetController::class, 'update']);
        Route::delete('/budgets/{id}', [BudgetController::class, 'destroy']);

        // --- Future Planner (Plans & Nested Task Items) ---
        Route::get('/future-planner', [FuturePlanController::class, 'index']);
        Route::post('/future-planner', [FuturePlanController::class, 'store']);
        Route::get('/future-planner/{futurePlan}', [FuturePlanController::class, 'show']);
        Route::put('/future-planner/{futurePlan}', [FuturePlanController::class, 'update']);
        Route::delete('/future-planner/{futurePlan}', [FuturePlanController::class, 'destroy']);
        Route::post('/future-planner/{futurePlan}/items', [FuturePlanController::class, 'storeItem']);
        Route::put('/future-planner/{futurePlan}/items/{item}', [FuturePlanController::class, 'updateItem']);
        Route::patch('/future-planner/{futurePlan}/items/{item}/reparent', [FuturePlanController::class, 'reparentItem']);
        Route::patch('/future-planner/{futurePlan}/items/{item}/toggle', [FuturePlanController::class, 'toggleItem']);
        Route::delete('/future-planner/{futurePlan}/items/{item}', [FuturePlanController::class, 'destroyItem']);

        // --- Asisten Finansial AI ---
        Route::prefix('ai')->group(function () {
            Route::post('/parse', [AiChatController::class, 'parse']);
            Route::post('/save', [AiChatController::class, 'save']);
            Route::post('/save-budget', [AiChatController::class, 'saveBudget']);
        });

        // --- Master Mind / Admin (wahyusnjy) ---
        Route::prefix('admin')->group(function () {
            Route::get('/users', [UserApprovalController::class, 'index']);
            Route::post('/users/{user}/approve', [UserApprovalController::class, 'approve']);
            Route::post('/users/{user}/reject', [UserApprovalController::class, 'reject']);
        });
    });
});

// Legacy backward-compatibility routes
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/ai/parse-chat', [AiChatController::class, 'parse'])->middleware('auth:sanctum');
Route::post('/ai/save-transaction', [AiChatController::class, 'save'])->middleware('auth:sanctum');

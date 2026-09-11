<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AiChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if(Auth::check()){
        return redirect()->route('dashboard');
    }else{
        return view('auth.login');
    }
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('transaksi')->group(function () {
    Route::get('/',[TransactionController::class,'index'])->name('transaction.index')->middleware(['auth', 'verified']);
    Route::post('/store',[TransactionController::class,'store'])->name('transaction.store');
    Route::post('/import',[TransactionController::class,'import'])->name('transaction.import');
})->middleware(['auth', 'verified']);

Route::prefix('saving')->group(function () {
    Route::get('/',[SavingController::class,'index'])->name('saving.index')->middleware(['auth', 'verified']);
    Route::post('/store',[SavingController::class,'store'])->name('saving.store');
    Route::put('/{id}',[SavingController::class,'update'])->name('saving.update');
    Route::delete('/{id}',[SavingController::class,'destroy'])->name('saving.destroy');
    Route::get('/detail/{id}',[SavingController::class,'detail'])->name('saving.detail');
})->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('ai-chat')->group(function () {
    Route::post('/parse', [AiChatController::class, 'parse'])->name('ai.parse');
    Route::post('/save', [AiChatController::class, 'save'])->name('ai.save');
});

require __DIR__.'/auth.php';

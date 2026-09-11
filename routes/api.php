<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SavingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/ai/parse-chat', [App\Http\Controllers\AiChatController::class, 'parse']);
Route::post('/ai/save-transaction', [App\Http\Controllers\AiChatController::class, 'save']);

Route::get('/getMonthsApi/{id}',[SavingController::class,'getMonthsApi'])->name('saving.getMonthsApi');
Route::get('/getTransactionApi/{id}', [SavingController::class, 'getTransactionApi'])->name('saving.getTransactionApi');

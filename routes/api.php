<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\RateLimitTransactions;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
    Route::put('/accounts/{id}', [AccountController::class, 'update']);
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/top-users-transactions', [TransactionController::class, 'topUsersTransactions']);
    Route::get('/balance', [TransactionController::class, 'balance']);
    Route::post('/transfer', [TransactionController::class, 'transfer'])->middleware(RateLimitTransactions::class);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store'])->middleware(RateLimitTransactions::class);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
    Route::get('/fees/{id}', [FeeController::class, 'show'])->name('fees.show');
    Route::put('/fees/{id}', [FeeController::class, 'update'])->name('fees.update');
    Route::delete('/fees/{id}', [FeeController::class, 'destroy'])->name('fees.destroy');
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

require __DIR__.'/auth.php';
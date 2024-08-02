<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Middleware\RateLimitTransactions;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
    Route::put('/accounts/{id}', [AccountController::class, 'update']);
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);

    Route::post('/transfer', [TransferController::class, 'transfer'])->middleware(RateLimitTransactions::class);
    
    Route::get('/balance', [BalanceController::class, 'balance']);
    
    Route::get('/transactions/top-users', [TransactionController::class, 'topUsersTransactions']);
    
    Route::get('/transactions/successful-per-hour', [TransactionController::class, 'successfulTransactionsPerHour']);
    
    Route::get('/transactions/amount-per-user-per-month', [TransactionController::class, 'amountPerUserPerMonth']);
    
    Route::get('/transactions/amount-per-account-per-user-per-month', [TransactionController::class, 'amountPerAccountPerUserPerMonth']);
    
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store'])->middleware(RateLimitTransactions::class);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');

});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

require __DIR__.'/auth.php';
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TransactionController extends Controller
{
    public function balance(Request $request)
    {
        $user = Auth::user();
        $accounts = $user->accounts;

        $totalBalance = 0;
        $accountBalances = [];

        foreach ($accounts as $account) {
            $accountBalance = $account->transactions->where('status', 'successful')->sum('amount');
            $accountBalances[] = [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'balance' => $accountBalance,
            ];
            $totalBalance += $accountBalance;
        }

        return response()->json([
            'total_balance' => $totalBalance,
            'accounts' => $accountBalances,
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        $transactions = Transaction::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return response()->json($transactions, 200);
    }

    public function store(StoreTransactionRequest $request)
{
    $user = Auth::user();
    $account = $user->accounts()->find($request->account_id);

    if (!$account) {
        return response()->json(['message' => 'Account not found or does not belong to the authenticated user.'], 404);
    }

    $today = Carbon::today()->toDateString();
    $cacheKey = "account:{$account->id}:transactions_total:{$today}";
    $dailyLimit = 50000001;

    $dailyTotal = Cache::remember($cacheKey, 86400, function () use ($account) {
        return Transaction::where('account_id', $account->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');
    });

    $newTransactionAmount = $request->amount;

    if (($dailyTotal + $newTransactionAmount) > $dailyLimit) {
        return response()->json(['message' => 'Daily transaction limit exceeded.'], 403);
    }

    $transaction = Transaction::create([
        'account_id' => $account->id,
        'amount' => $newTransactionAmount,
        'status' => $request->status,
    ]);

    Cache::increment($cacheKey, $newTransactionAmount);

    return response()->json($transaction, 201);
}

    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return response()->json($transaction, 200);
    }

    public function update(UpdateTransactionRequest $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->update($request->validated());

        return response()->json($transaction, 200);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }
}

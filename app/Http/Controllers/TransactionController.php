<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function transfer(Request $request)
    {
        $request->validate([
            'source_account_number' => 'required|string|exists:accounts,account_number',
            'destination_account_number' => 'required|string|exists:accounts,account_number',
            'amount' => 'required|numeric|digits_between:4,8',
        ]);

        $sourceAccount = Account::where('account_number', $request->source_account_number)->first();
        $destinationAccount = Account::where('account_number', $request->destination_account_number)->first();

        if ($sourceAccount->id === $destinationAccount->id) {
            return response()->json(['message' => 'Source and destination accounts cannot be the same.'], 400);
        }

        $sourceBalance = $sourceAccount->inventory;

        if ($sourceBalance < $request->amount) {
            return response()->json(['message' => 'Insufficient funds in the source account.'], 403);
        }

        DB::transaction(function () use ($sourceAccount, $destinationAccount, $request) {

            $transfer = Transfer::create([
                'source_account_id' => $sourceAccount->id,
                'destination_account_id' => $destinationAccount->id,
                'amount' => $request->amount,
                'status' => 'completed',
            ]);

            $sourceAccount->transactions()->create([
                'amount' => -$request->amount,
                'status' => 'successful',
            ]);

            $destinationAccount->transactions()->create([
                'amount' => $request->amount,
                'status' => 'successful',
            ]);
        });

        return response()->json(['message' => 'Transfer successful'], 200);
    }

    public function balance(Request $request)
    {
        $user = Auth::user();
        $accounts = $user->accounts;

        $totalBalance = 0;
        $accountBalances = [];

        foreach ($accounts as $account) {
            $accountBalance = $account->transactions->where('status', 'successful')->sum('amount');
            $inventory = $account->inventory;
            $accountBalances[] = [
                'inventory' => $inventory,
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
                ->whereDate('created_at', $today)
                ->sum('amount');
        });

        $newTransactionAmount = $request->amount;

        if (($dailyTotal + $newTransactionAmount) > $dailyLimit) {
            return response()->json(['message' => 'Daily transaction limit exceeded.'], 403);
        }

        if ($request->status === 'successful') {
            $account->increment('inventory', $newTransactionAmount);
        }

        $transaction = Transaction::create($request->validated());

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

<?php

namespace App\Http\Controllers;

use App\Events\TransactionProcessed;
use App\Events\TransferCompleted;
use App\Http\Requests\StoreTransactionRequest;
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
            'status' => 'required|in:successful,failed',
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

            $sourceAccount->update(['inventory' => $sourceAccount->inventory - $request->amount]);

            $destinationAccount->update(['inventory' => $destinationAccount->inventory + $request->amount]);

            $sourceAccount->transactions()->create([
                'amount' => -$request->amount,
                'status' =>  $request->status,
            ]);

            $destinationAccount->transactions()->create([
                'amount' => $request->amount,
                'status' =>  $request->status,
            ]);

            $transfer = Transfer::create([
                'source_account_id' => $sourceAccount->id,
                'destination_account_id' => $destinationAccount->id,
                'amount' => $request->amount,
                'status' => $request->status,
            ]);
            
            event(new TransferCompleted($transfer));            
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
        $dailyLimit = 50000000;

        $dailyTotal = Cache::remember($cacheKey, 86400, function () use ($account, $today) {
            return Transaction::where('account_id', $account->id)
                ->whereDate('created_at', $today)
                ->sum('amount');
        });

        $newTransactionAmount = $request->amount;

        if (($dailyTotal + $newTransactionAmount) > $dailyLimit) {
            return response()->json(['message' => 'Daily transaction limit exceeded.'], 403);
        }

        $transaction = Transaction::create($request->validated());

        if ($request->status === 'successful') {
            $account->decrement('inventory', $newTransactionAmount);
            event(new TransactionProcessed($transaction));
        }

        Cache::increment($cacheKey, $newTransactionAmount);

        return response()->json($transaction, 201);
    }

    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return response()->json($transaction, 200);
    }

}

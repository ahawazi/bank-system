<?php

namespace App\Http\Controllers;

use App\Events\TransactionProcessed;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class TransactionController extends Controller
{
    public function topUsersTransactions(Request $request)
    {
        $timeLimit = Carbon::now()->subMinutes(10);

        $topUserIds = Transaction::where('transactions.created_at', '>=', $timeLimit)
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->select('accounts.user_id', \DB::raw('COUNT(transactions.id) as transaction_count'))
            ->groupBy('accounts.user_id')
            ->orderBy('transaction_count', 'desc')
            ->limit(3)
            ->pluck('accounts.user_id');

        $userTransactions = User::whereIn('id', $topUserIds)
            ->with(['accounts.transactions' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->get();

        $responseData = $userTransactions->map(function ($user) {
            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'transaction_count' => $user->accounts->flatMap->transactions->count(),
                'accounts_id' => $user->accounts,
                'last_10_transactions' => $user->accounts->flatMap->transactions->map(function ($transaction) {
                    return [
                        'transaction_id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at->toDateTimeString(),
                    ];
                }),
            ];
        });

        return response()->json($responseData, 200);
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

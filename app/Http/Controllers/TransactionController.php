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
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function amountPerAccountPerUserPerMonth()
    {
        $summarizedData = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->select(DB::raw("accounts.user_id, accounts.account_number, transactions.account_id, DATE_FORMAT(transactions.created_at, '%Y-%m') as month, SUM(transactions.amount) as total_amount"))
            ->groupBy('accounts.user_id', 'accounts.account_number', 'transactions.account_id', 'month')
            ->orderBy('accounts.user_id', 'asc')
            // ->orderBy('accounts.account_number', 'asc')
            ->orderBy('transactions.account_id', 'asc')
            ->orderBy('month', 'asc')
            ->get();
    
        $transactions = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->select('accounts.user_id', 'accounts.account_number', 'transactions.account_id', 'transactions.amount', 'transactions.created_at', DB::raw("DATE_FORMAT(transactions.created_at, '%Y-%m') as month"))
            ->orderBy('accounts.user_id', 'asc')
            ->orderBy('accounts.account_number', 'asc')
            ->orderBy('transactions.created_at', 'asc')
            ->get();
    
        $response = [];
    
        foreach ($summarizedData as $summary) {
            $userId = $summary->user_id;
            $accountId = $summary->account_id;
            $month = $summary->month;
            $accountNumber = $summary->account_number;
    
            $userTransactions = $transactions->filter(function ($transaction) use ($userId, $accountId, $month) {
                return $transaction->user_id == $userId && $transaction->account_id == $accountId && $transaction->month == $month;
            });
    
            $response[] = [
                'user_id' => $userId,
                'account_id' => $accountId,
                'month' => $month,
                'account_number' => $accountNumber,
                'total_amount' => $summary->total_amount,
                'transactions' => $userTransactions->values()->all(),
            ];
        }
    
        return response()->json($response);
    }    

    public function amountPerUserPerMonth()
    {
        $results = DB::table('transactions')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->select(DB::raw("accounts.user_id, transactions.account_id, DATE_FORMAT(transactions.created_at, '%Y-%m') as month, SUM(transactions.amount) as total_amount"))
            ->groupBy('accounts.user_id', 'transactions.account_id', 'month')
            ->orderBy('accounts.user_id', 'asc')
            ->orderBy('transactions.account_id', 'asc')
            ->orderBy('month', 'asc')
            ->get();
    
        return response()->json($results);
    }
    
    public function successfulTransactionsPerHour()
    {
        $results = DB::table('transactions')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, COUNT(*) as transaction_count"))
            ->where('status', 'successful')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json($results);
    }

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

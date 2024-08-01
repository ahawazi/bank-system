<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
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

}

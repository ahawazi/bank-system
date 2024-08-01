<?php

namespace App\Http\Controllers;

use App\Events\TransferCompleted;
use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
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
}

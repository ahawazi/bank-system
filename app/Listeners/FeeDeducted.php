<?php

namespace App\Listeners;

use App\Events\TransactionProcessed;
use App\Events\TransferCompleted;
use App\Models\Fee;
use App\Models\Account;

class FeeDeducted
{
    public function handle($event)
    {
        $feeAmount = 500;

        if ($event instanceof TransactionProcessed) {
            $transaction = $event->transaction;
            $account = $transaction->account;
        } elseif ($event instanceof TransferCompleted) {
            $transfer = $event->transfer;
            $account = $transfer->sourceAccount;
        }

        if ($account && $account->inventory >= $feeAmount) {
            $account->inventory -= $feeAmount;
            $account->save();
            
            Fee::create([
                'transaction_id' => $transaction->id ?? null,
                'transfer_id' => $transfer->id ?? null,
                'fee_amount' => $feeAmount,
            ]);
        } else {
            return response()->json(['message' => 'Dont have mony']);
        }
    }
}

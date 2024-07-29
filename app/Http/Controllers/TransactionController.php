<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $accounts = Transaction::all();
        return response()->json($accounts, 200);
    }

    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->validated());
        return response()->json($transaction, 201);
    }

    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return response()->json($transaction, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

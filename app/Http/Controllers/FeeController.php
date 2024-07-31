<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeRequest;
use App\Http\Requests\UpdateFeeRequest;
use App\Models\Fee;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class FeeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
    
        $transactions = Transaction::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();
    
        $fees = Fee::whereIn('transaction_id', $transactions->pluck('id'))->get();
    
        return response()->json($fees, 200);
    }

    public function store(StoreFeeRequest $request)
    {
        $fee = Fee::create($request->validated());
        return response()->json($fee, 201);
    }

    public function show($id)
    {
        $fee = Fee::findOrFail($id);
        return response()->json($fee, 200);
    }

    public function update(UpdateFeeRequest $request, $id)
    {
        $fee = Fee::findOrFail($id);

        $fee->update($request->validated());

        return response()->json($fee, 200);
    }

    public function destroy($id)
    {
        $fee = Fee::findOrFail($id);

        $fee->delete();

        return response()->json(['message' => 'Fee deleted successfully'], 200);
    }
}

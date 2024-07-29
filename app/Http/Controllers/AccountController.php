<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        // $accounts = Auth::user()->accounts();
        $accounts = Account::all();
        return response()->json($accounts, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|unique:accounts|max:255',
            'phone_number' => 'required|string|max:15',
        ]);

        $account = Auth::user()->accounts()->create([
            'account_number' => $request->account_number,
            'phone_number' => $request->phone_number,
        ]);

        return response()->json($account, 201);
    }
    
    public function show($id)
    {
        $account = Auth::user()->accounts()->findOrFail($id);
        return response()->json($account, 200);
    }

    public function update(UpdateAccountRequest $request, $id)
    {
        $account = Auth::user()->accounts()->findOrFail($id);
        $account->update($request->validated());
        return response()->json($account, 200);
    }

    public function destroy($id)
    {
        $account = Auth::user()->accounts()->findOrFail($id);
        $account->delete();
        return response()->json(['message' => 'Account deleted successfully'], 200);
    }
}

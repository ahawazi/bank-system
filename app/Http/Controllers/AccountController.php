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
        $user = Auth::user();
        $accounts = Account::where('user_id', $user->id)->get();
        return response()->json($accounts, 200);
    }

    public function store(StoreAccountRequest $request)
    {
        $account = Auth::user()->accounts()->create($request->validated());

        return response()->json($account, 201);
    }

    public function show($id)
    {
        $account = Auth::user()->accounts()->findOrFail($id);
        return response()->json($account, 200);
    }

    public function update(UpdateAccountRequest $request, $id): JsonResponse
    {
        $account = Auth::user()->accounts()->findOrFail($id)->update($request->validated());

        return response()->json($account, 200);
    }

    public function destroy($id)
    {
        $account = Auth::user()->accounts()->findOrFail($id);
        $account->delete();
        return response()->json(['message' => 'Account deleted successfully'], 200);
    }
}

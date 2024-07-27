<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Auth::user()->accounts;
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

    public function update(UpdateAccountRequest $request, $id)
    {
        $account = Auth::user()->accounts()->findOrFail($id);
        $account->update($request->validated());
        return response()->json($account, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

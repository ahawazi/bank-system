<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeRequest;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fee = Fee::all();
        return response()->json($fee, 200);
    }

    public function store(StoreFeeRequest $request)
    {
        $fee = Fee::create($request->validated());
        return response()->json($fee, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

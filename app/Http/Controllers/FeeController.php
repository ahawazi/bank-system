<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeRequest;
use App\Http\Requests\UpdateFeeRequest;
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

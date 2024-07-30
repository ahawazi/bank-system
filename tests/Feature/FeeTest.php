<?php

use App\Models\Fee;
use App\Models\Transaction;
use function Pest\Laravel\{get, post, put, delete};

it('can list fees', function () {
    Fee::factory()->count(3)->create();

    get('/api/fees')
        ->assertStatus(200)
        ->assertJsonCount(3);
});

it('can create a fee', function () {
    $transaction = Transaction::factory()->create();
    $feeData = Fee::factory()->make(['transaction_id' => $transaction->id])->toArray();

    post('/api/fees', $feeData)
        ->assertStatus(201)
        ->assertJsonPath('fee_amount', $feeData['fee_amount']);
});

it('can show a fee', function () {
    $fee = Fee::factory()->create();

    get("/api/fees/{$fee->id}")
        ->assertStatus(200)
        ->assertJsonPath('fee_amount', $fee->fee_amount);
});

it('can update a fee', function () {
    $fee = Fee::factory()->create();
    $updateData = ['fee_amount' => 75.00];

    put("/api/fees/{$fee->id}", $updateData)
        ->assertStatus(200)
        ->assertJsonPath('fee_amount', $updateData['fee_amount']);
});

it('can delete a fee', function () {
    $fee = Fee::factory()->create();

    delete("/api/fees/{$fee->id}")
        ->assertStatus(204);

    expect(Fee::find($fee->id))->toBeNull();
});

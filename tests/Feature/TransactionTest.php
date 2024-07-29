<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\{actingAs, get, post, put, delete};

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    actingAs($this->user, 'sanctum');
});

it('can list transactions', function () {
    Transaction::factory()->count(3)->create();

    get('/api/transactions')
        ->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('can create a transaction', function () {
    $transactionData = Transaction::factory()->make()->toArray();

    post('/api/transactions', $transactionData)
        ->assertStatus(201)
        ->assertJsonPath('data.amount', $transactionData['amount']);
});

it('can show a transaction', function () {
    $transaction = Transaction::factory()->create();

    get("/api/transactions/{$transaction->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.amount', $transaction->amount);
});

it('can update a transaction', function () {
    $transaction = Transaction::factory()->create();
    $updateData = [
        'amount' => 2000.00,
        'description' => 'Updated description',
    ];

    put("/api/transactions/{$transaction->id}", $updateData)
        ->assertStatus(200)
        ->assertJsonPath('data.amount', 2000.00);
});

it('can delete a transaction', function () {
    $transaction = Transaction::factory()->create();

    delete("/api/transactions/{$transaction->id}")
        ->assertStatus(200);

    expect(Transaction::find($transaction->id))->toBeNull();
});

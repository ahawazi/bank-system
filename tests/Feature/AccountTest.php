<?php

use App\Models\User;
use App\Models\Account;
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

it('can list accounts', function () {
    Account::factory()->count(3)->create(['user_id' => $this->user->id]);

    get('/api/accounts')
        ->assertStatus(200)
        ->assertJsonCount(3);
});

it('can create an account', function () {
    $accountData = Account::factory()->make()->toArray();

    post('/api/accounts', $accountData)
        ->assertStatus(201)
        ->assertJsonPath('account_number', $accountData['account_number']);
});

it('can show an account', function () {
    $account = Account::factory()->create(['user_id' => $this->user->id]);

    get("/api/accounts/{$account->id}")
        ->assertStatus(200)
        ->assertJsonPath('account_number', $account->account_number);
});

it('can update an account', function () {
    $account = Account::factory()->create(['user_id' => $this->user->id]);
    $updateData = [
        'account_number' => 'NEW_NUMBER',
        'phone_number' => '1234567890'
    ];

    put("/api/accounts/{$account->id}", $updateData)
        ->assertStatus(200)
        ->assertJsonPath('account_number', 'NEW_NUMBER');
});

it('can delete an account', function () {
    $account = Account::factory()->create(['user_id' => $this->user->id]);

    delete("/api/accounts/{$account->id}")
        ->assertStatus(200);

    expect(Account::find($account->id))->toBeNull();
});

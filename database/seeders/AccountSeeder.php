<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {
            Account::factory(2)->create(['user_id' => $user->id]);
        });
    }
}

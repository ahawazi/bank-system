<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->call([
            // UserSeeder::class,
            // AccountSeeder::class,
            // TransactionSeeder::class,
            // FeeSeeder::class,
            // TransferSeeder::class,
        ]);

        // Account::factory(5)->create();
        // User::factory(5)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transfer>
 */
class TransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_account_id' => Account::factory(),
            'destination_account_id' => Account::factory(),
            // 'amount' => $this->faker->numberBetween(1000, 5000000),
            'amount' => $this->faker->randomFloat(0, 1000, 50000001),
            'status' => $this->faker->randomElement(['successful', 'failed']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

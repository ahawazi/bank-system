<?php

namespace Database\Seeders;

use App\Models\Transfer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransferSeeder extends Seeder
{
    public function run(): void
    {
        Transfer::factory()->count(1000)->create();
    }
}

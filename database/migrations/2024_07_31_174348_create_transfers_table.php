<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('destination_account_id')->constrained('accounts')->onDelete('cascade');
            $table->decimal('amount', 8, 0);
            $table->enum('status', ['successful', 'failed']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};

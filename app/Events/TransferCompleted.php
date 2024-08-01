<?php

namespace App\Events;

use App\Models\Transfer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCompleted
{
    use Dispatchable, SerializesModels;

    public $transfer;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }
}

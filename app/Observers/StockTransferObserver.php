<?php

namespace App\Observers;

use App\Models\Inventory\StockTransfer;

class StockTransferObserver
{
    public function creating(StockTransfer $transfer): void
    {
        if (empty($transfer->transfer_number)) {
            $transfer->transfer_number = $transfer->generateTransferNumber();
        }
    }
}

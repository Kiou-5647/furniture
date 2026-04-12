<?php

namespace App\Actions\Sales;

use App\Enums\RefundStatus;
use App\Models\Hr\Employee;
use App\Models\Sales\Refund;
use Illuminate\Support\Facades\DB;

class ProcessRefundAction
{
    public function approve(Refund $refund, Employee $processor): Refund
    {
        return DB::transaction(function () use ($refund, $processor) {
            $refund->update([
                'status' => RefundStatus::Completed,
                'processed_by' => $processor->id,
                'processed_at' => now(),
            ]);

            return $refund->refresh();
        });
    }

    public function reject(Refund $refund, Employee $processor, string $notes = ''): Refund
    {
        return DB::transaction(function () use ($refund, $processor, $notes) {
            $refund->update([
                'status' => RefundStatus::Rejected,
                'processed_by' => $processor->id,
                'processed_at' => now(),
                'notes' => $notes,
            ]);

            return $refund->refresh();
        });
    }

    public function markProcessing(Refund $refund, Employee $processor): Refund
    {
        $refund->update([
            'status' => RefundStatus::Processing,
            'processed_by' => $processor->id,
        ]);

        return $refund->refresh();
    }
}

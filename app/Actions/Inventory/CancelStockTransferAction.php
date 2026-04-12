<?php

namespace App\Actions\Inventory;

use App\Enums\StockMovementType;
use App\Enums\StockTransferStatus;
use App\Models\Hr\Employee;
use App\Models\Inventory\StockTransfer;
use Illuminate\Support\Facades\DB;

class CancelStockTransferAction
{
    public function __construct(
        private RecordStockMovementAction $recordMovement,
    ) {}

    public function handle(
        StockTransfer $transfer,
        Employee $performedBy,
    ): StockTransfer {
        if (! in_array($transfer->status, [StockTransferStatus::Draft, StockTransferStatus::InTransit], true)) {
            throw new \RuntimeException('Chỉ có thể hủy phiếu chuyển kho ở trạng thái Nháp hoặc Đang vận chuyển.');
        }

        return DB::transaction(function () use ($transfer, $performedBy) {
            if ($transfer->status === StockTransferStatus::InTransit) {
                $transfer->load('items.variant', 'fromLocation');

                foreach ($transfer->items as $item) {
                    $costPerUnit = $item->unit_cost ? (float) $item->unit_cost : null;

                    $this->recordMovement->handle(
                        variant: $item->variant,
                        location: $transfer->fromLocation,
                        type: StockMovementType::TransferIn,
                        quantity: $item->quantity_shipped,
                        notes: 'Hủy chuyển kho - hoàn trả tồn kho',
                        performedBy: $performedBy,
                        referenceType: StockTransfer::class,
                        referenceId: $transfer->id,
                        costPerUnit: $costPerUnit,
                    );
                }
            }

            $transfer->update(['status' => StockTransferStatus::Cancelled]);

            return $transfer->fresh();
        });
    }
}

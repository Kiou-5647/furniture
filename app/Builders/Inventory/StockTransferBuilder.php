<?php

namespace App\Builders\Inventory;

use App\Enums\StockTransferStatus;
use Illuminate\Database\Eloquent\Builder;

class StockTransferBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('transfer_number', 'ilike', "%{$search}%");
        });
    }

    public function byStatus(StockTransferStatus $status): self
    {
        return $this->where('status', $status);
    }

    public function byFromLocation(string $locationId): self
    {
        return $this->where('from_location_id', $locationId);
    }

    public function byToLocation(string $locationId): self
    {
        return $this->where('to_location_id', $locationId);
    }

    public function byDateRange(?string $from, ?string $to): self
    {
        return $this
            ->when($from, fn (self $q) => $q->where('created_at', '>=', $from))
            ->when($to, fn (self $q) => $q->where('created_at', '<=', $to));
    }
}

<?php

namespace App\Builders\Inventory;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Builder;

class StockMovementBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('notes', 'ilike', "%{$search}%");
        });
    }

    public function byType(StockMovementType $type): self
    {
        return $this->where('type', $type);
    }

    public function byLocation(string $locationId): self
    {
        return $this->where('location_id', $locationId);
    }

    public function byVariant(string $variantId): self
    {
        return $this->where('variant_id', $variantId);
    }

    public function byDateRange(?string $from, ?string $to): self
    {
        return $this
            ->when($from, fn (self $q) => $q->where('created_at', '>=', $from))
            ->when($to, fn (self $q) => $q->where('created_at', '<=', $to));
    }
}

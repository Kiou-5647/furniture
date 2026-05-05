<?php

namespace App\Builders\Sales;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

class OrderBuilder extends Builder
{
    public function pending(): self
    {
        return $this->where('status', 'pending');
    }

    public function processing(): self
    {
        return $this->where('status', 'processing');
    }

    public function completed(): self
    {
        return $this->where('status', 'completed');
    }

    public function cancelled(): self
    {
        return $this->where('status', 'cancelled');
    }

    public function byStatus(OrderStatus $status): self
    {
        return $this->where('status', $status->value);
    }

    public function bySource(string $source): self
    {
        return $this->where('source', $source);
    }

    public function byStoreLocation(string $locationId): self
    {
        return $this->where('store_location_id', $locationId);
    }

    public function byCustomerId(string $customerId): self
    {
        return $this->where('customer_id', $customerId);
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('order_number', 'ilike', "%{$search}%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('full_name', 'ilike', "%{$search}%");
                });
        });
    }
}

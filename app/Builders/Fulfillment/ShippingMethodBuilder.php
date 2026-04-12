<?php

namespace App\Builders\Fulfillment;

use Illuminate\Database\Eloquent\Builder;

class ShippingMethodBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'ilike', "%{$search}%")
                ->orWhere('code', 'ilike', "%{$search}%");
        });
    }

    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function byActiveStatus(bool $isActive): self
    {
        return $this->where('is_active', $isActive);
    }
}

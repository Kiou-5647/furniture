<?php

namespace App\Builders\Sales;

use Illuminate\Database\Eloquent\Builder;

class PaymentBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('transaction_id', 'ilike', "%{$search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('full_name', 'ilike', "%{$search}%"));
        });
    }
}

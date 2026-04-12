<?php

namespace App\Builders\Sales;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Builder;

class InvoiceBuilder extends Builder
{
    public function search(string $search): self
    {
        return $this->where('invoice_number', 'ilike', "%{$search}%");
    }

    public function byStatus(InvoiceStatus $status): self
    {
        return $this->where('status', $status->value);
    }

    public function byType(InvoiceType $type): self
    {
        return $this->where('type', $type->value);
    }

    public function byInvoiceableType(string $type): self
    {
        return $this->where('invoiceable_type', $type);
    }

    public function withRemainingBalance(float $min = 0): self
    {
        return $this->whereRaw('amount_due - amount_paid > ?', [$min]);
    }
}

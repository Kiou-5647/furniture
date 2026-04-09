<?php

namespace App\Data\Sales;

use App\Enums\InvoiceType;

readonly class CreateInvoiceData
{
    public function __construct(
        public string $invoiceable_type,
        public string $invoiceable_id,
        public InvoiceType $type,
        public string $amount_due,
        public ?string $validated_by = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            invoiceable_type: $data['invoiceable_type'],
            invoiceable_id: $data['invoiceable_id'],
            type: InvoiceType::from($data['type']),
            amount_due: $data['amount_due'],
            validated_by: $data['validated_by'] ?? null,
        );
    }
}

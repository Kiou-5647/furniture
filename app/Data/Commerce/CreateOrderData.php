<?php

namespace App\Data\Commerce;

use Illuminate\Http\Request;

readonly class CreateOrderData
{
    public function __construct(
        public string $customer_id,
        public string $shipping_address_id,
        public array $items,
        public ?string $shipping_method_id = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            customer_id: $request->string('customer_id'),
            shipping_address_id: $request->string('shipping_address_id'),
            items: $request->input('items', []),
            shipping_method_id: $request->string('shipping_method_id')->toString() ?: null,
        );
    }
}

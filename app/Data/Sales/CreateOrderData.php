<?php

namespace App\Data\Sales;

use Illuminate\Http\Request;

readonly class CreateOrderData
{
    public function __construct(
        public ?string $customer_id = null,
        public array $items = [],
        public ?string $shipping_method_id = null,
        public ?string $guest_name = null,
        public ?string $guest_phone = null,
        public ?string $guest_email = null,
        public ?string $notes = null,
        public string $source = 'online',
        public ?string $store_location_id = null,
        public ?string $shipping_cost = null,
        public ?string $province_code = null,
        public ?string $ward_code = null,
        public ?string $province_name = null,
        public ?string $ward_name = null,
        public ?string $building = null,
        public ?string $address_number = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            customer_id: $request->string('customer_id')->toString() ?: null,
            items: $request->input('items', []),
            shipping_method_id: $request->string('shipping_method_id')->toString() ?: null,
            guest_name: $request->string('guest_name')->toString() ?: null,
            guest_phone: $request->string('guest_phone')->toString() ?: null,
            guest_email: $request->string('guest_email')->toString() ?: null,
            notes: $request->string('notes')->toString() ?: null,
            source: $request->string('source')->toString() ?: 'online',
            store_location_id: $request->string('store_location_id')->toString() ?: null,
            shipping_cost: $request->string('shipping_cost')->toString() ?: null,
            province_code: $request->string('province_code')->toString() ?: null,
            ward_code: $request->string('ward_code')->toString() ?: null,
            province_name: $request->string('province_name')->toString() ?: null,
            ward_name: $request->string('ward_name')->toString() ?: null,
            building: $request->string('building')->toString() ?: null,
            address_number: $request->string('address_number')->toString() ?: null,
        );
    }
}

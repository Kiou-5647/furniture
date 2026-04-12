<?php

namespace App\Data\Fulfillment;

readonly class CreateShippingMethodData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $price,
        public ?int $estimated_delivery_days = null,
        public bool $is_active = true,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: $request->string('code')->toString(),
            name: $request->string('name')->toString(),
            price: $request->string('price')->toString(),
            estimated_delivery_days: $request->integer('estimated_delivery_days') ?: null,
            is_active: $request->boolean('is_active'),
        );
    }
}

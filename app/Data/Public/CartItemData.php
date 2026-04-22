<?php

namespace App\Data\Public;

use Illuminate\Http\Request;

readonly class CartItemData
{
    public function __construct(
        public string $purchasable_type,
        public string $purchasable_id,
        public int $quantity,
        public array $configuration = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            purchasable_type: $request->string('purchasable_type'),
            purchasable_id: $request->string('purchasable_id'),
            quantity: $request->integer('quantity'),
            configuration: $request->input('configuration', []),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            purchasable_type: $data['purchasable_type'],
            purchasable_id: $data['purchasable_id'],
            quantity: $data['quantity'],
            configuration: $data['configuration'] ?? [],
        );
    }
}

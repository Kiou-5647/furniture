<?php

namespace App\Data\Public;

readonly class CartTotalsData
{
    public function __construct(
        public int $item_count,
        public float $subtotal,
        public float $discount,
        public float $total,
        public array $line_items,
    ) {}
}

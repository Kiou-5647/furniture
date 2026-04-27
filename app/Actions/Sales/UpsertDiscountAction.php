<?php

namespace App\Actions\Sales;

use App\Models\Sales\Discount;

class UpsertDiscountAction
{
    /**
     * Create a new discount or update an existing one.
     *
     * @param array $data Validated data from the Request
     * @param Discount|null $discount The discount instance to update, or null to create a new one
     * @return Discount
     */
    public function execute(array $data, ?Discount $discount = null): Discount
    {
        if ($discount) {
            $discount->update($data);
            return $discount;
        }

        return Discount::create($data);
    }
}

<?php

namespace App\Actions\Customer;

use App\Models\Customer\CartItem;

class RemoveFromCartAction
{
    public function execute(CartItem $item): void
    {
        $item->delete();
    }
}

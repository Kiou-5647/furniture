<?php

namespace App\Actions\Customer;

use App\Models\Customer\Cart;

class ClearCartAction
{
    public function execute(Cart $cart): void
    {
        $cart->items()->delete();
    }
}

<?php

namespace App\Actions\Public;

use App\Models\Public\Cart;

class ClearCartAction
{
    public function execute(Cart $cart): void
    {
        $cart->items()->delete();
    }
}

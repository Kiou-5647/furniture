<?php

namespace App\Actions\Public;

use App\Models\Public\CartItem;

class RemoveFromCartAction
{
    public function execute(CartItem $item): void
    {
        $item->delete();
    }
}

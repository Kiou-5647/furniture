<?php

namespace App\Actions\Public;

use App\Models\Public\CartItem;

class UpdateCartItemAction
{
    public function execute(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $item->delete();

            throw new \InvalidArgumentException('Số lượng phải lớn hơn 0. Mục đã bị xóa.');
        }

        $item->updateQuietly(['quantity' => $quantity]);
        $item->syncPriceFromPurchasable();

        return $item->fresh();
    }
}

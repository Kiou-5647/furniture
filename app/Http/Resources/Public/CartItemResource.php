<?php

namespace App\Http\Resources\Public;

use App\Models\Product\Bundle;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $name = 'Unknown Item';
        $image = null;

        if ($this->purchasable instanceof ProductVariant) {
            $name = "{$this->purchasable->product->name} - {$this->purchasable->name}";
            $image = $this->purchasable->getFirstMediaUrl('primary_image', 'thumb');
        } elseif ($this->purchasable instanceof Bundle) {
            $name = $this->purchasable->name;
            $image = $this->purchasable->getFirstMediaUrl('primary_image', 'thumb');
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->getSubtotal(),
            'configuration' => $this->configuration,
            'image_url' => $image,
        ];
    }
}

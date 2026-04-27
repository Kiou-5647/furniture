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
        $sku = '';
        $image = null;
        $selectedVariants = null;

        if ($this->purchasable instanceof ProductVariant) {
            $name = "{$this->purchasable->product->name} - {$this->purchasable->name}";
            $sku = $this->purchasable->sku;
            $image = $this->purchasable->getFirstMediaUrl('primary_image', 'thumb');
        } elseif ($this->purchasable instanceof Bundle) {
            $name = $this->purchasable->name;
            $image = $this->purchasable->getFirstMediaUrl('primary_image', 'thumb');

            if ($this->configuration) {
                $selectedVariants = [];
                foreach ($this->purchasable->contents as $content) {
                    $variantId = $this->configuration[$content->id] ?? null;
                    if ($variantId) {
                        /** @var \App\Models\Product\ProductVariant $variant */
                        $variant = ProductVariant::find($variantId);
                        if ($variant) {
                            $selectedVariants[] = [
                                'id' => $variant->id,
                                'name' => $variant->product->name . ' ' . $variant->name,
                                'label' => $variant->swatch_label,
                                'sku' => $variant->sku,
                                'slug' => $variant->slug,
                                'price' => $variant->price,
                                'sale_price' => $variant->getEffectivePrice(),
                                'quantity' => $content->quantity,
                                'image_url' => $variant->getFirstMediaUrl('primary_image', 'thumb'),
                            ];
                        }
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'sku' => $sku,
            'slug' => $this->purchasable->slug,
            'quantity' => $this->quantity,
            'unit_price' => $this->getEffectivePrice(),
            'subtotal' => (float) $this->getSubtotal(),
            'configuration' => $this->configuration,
            'image_url' => $image,
            'purchasable_type' => $this->purchasable_type,
            'selected_variants' => $selectedVariants,
        ];
    }
}

<?php

namespace App\Http\Resources\Public;

use App\Models\Product\Bundle;
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
        $originalPrice = 0.0;
        $availableStock = 0;

        if ($this->purchasable instanceof ProductVariant) {
            $name = "{$this->purchasable->product->name} - {$this->purchasable->name}";
            $sku = $this->purchasable->sku;
            $image = $this->purchasable->getFirstMediaUrl('primary_image', 'thumb');
            $originalPrice = (float) $this->purchasable->price;
            $availableStock = $this->purchasable->getTotalStock();
        } elseif ($this->purchasable instanceof Bundle) {
            $name = $this->purchasable->name;
            $image = $this->purchasable->getFirstMediaUrl('primary_image', 'thumb');

            if ($this->configuration) {
                $selectedVariants = [];
                $bundleOriginalTotal = 0.0;
                $minBundleStock = PHP_INT_MAX;

                foreach ($this->purchasable->contents as $content) {
                    $variantId = $this->configuration[$content->id] ?? null;
                    if ($variantId) {
                        /** @var \App\Models\Product\ProductVariant $variant */
                        $variant = ProductVariant::find($variantId);
                        if ($variant) {

                            $bundleOriginalTotal += (float) $variant->getEffectivePrice() * $content->quantity;

                            $possibleBundles = (int) floor($variant->getTotalStock() / $content->quantity);
                            $minBundleStock = min($minBundleStock, $possibleBundles);

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
                                'available_stock' => $variant->getTotalStock(),
                            ];
                        } else {
                            $minBundleStock = 0;
                        }
                    } else {
                        $minBundleStock = 0;
                    }
                }
                $originalPrice = $bundleOriginalTotal;
                $availableStock = ($minBundleStock === PHP_INT_MAX) ? 0 : $minBundleStock;
            }
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'sku' => $sku,
            'slug' => $this->purchasable->slug,
            'quantity' => $this->quantity,
            'unit_price' => $this->getEffectivePrice(),
            'original_unit_price' => $originalPrice,
            'is_available' => $this->validateAvailability(),
            'available_stock' => $availableStock,
            'subtotal' => (float) $this->getSubtotal(),
            'configuration' => $this->configuration,
            'image_url' => $image,
            'purchasable_type' => $this->purchasable_type,
            'purchasable_id' => $this->purchasable_id,
            'selected_variants' => $selectedVariants,
        ];
    }
}

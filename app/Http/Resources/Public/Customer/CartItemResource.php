<?php

namespace App\Http\Resources\Public\Customer;

use App\Models\Product\Bundle;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchasable_type' => $this->purchasable_type,
            'purchasable_id' => $this->purchasable_id,
            'purchasable' => $this->resolvePurchasable(),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => (float) $this->unit_price * $this->quantity,
        ];
    }

    protected function resolvePurchasable(): array
    {
        $purchasable = $this->purchasable;

        if (! $purchasable) {
            return [];
        }

        return match ($this->purchasable_type) {
            Product::class => $this->resolveProduct($purchasable),
            Bundle::class => $this->resolveBundle($purchasable),
            default => [],
        };
    }

    protected function resolveProduct(Product $product): array
    {
        $variant = $this->getVariantForProduct($product);

        return [
            'id' => $product->id,
            'type' => 'product',
            'name' => $product->name,
            'slug' => $product->slug,
            'variant_id' => $variant?->id,
            'variant_sku' => $variant?->sku,
            'variant_name' => $variant?->name,
            'image_url' => $variant?->getFirstMediaUrl('primary_image') ?: '',
            'image_thumb_url' => $variant?->getFirstMediaUrl('primary_image', 'thumb') ?: ''
        ];
    }

    protected function resolveBundle(Bundle $bundle): array
    {
        return [
            'id' => $bundle->id,
            'type' => 'bundle',
            'name' => $bundle->name,
            'slug' => $bundle->slug,
            'image_url' => $bundle->getFirstMediaUrl('primary_image') ?: '',
            'image_thumb_url' => $bundle->getFirstMediaUrl('primary_image', 'thumb') ?: ''
        ];
    }

    protected function getVariantForProduct(Product $product): ?ProductVariant
    {
        $variantId = $this->configuration['variant_id'] ?? null;

        if ($variantId) {
            return $product->variants()->find($variantId);
        }

        return $product->variants()->first();
    }
}

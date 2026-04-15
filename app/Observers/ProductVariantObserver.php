<?php

namespace App\Observers;

use App\Models\Product\ProductVariant;
use Illuminate\Support\Str;

class ProductVariantObserver
{
    public function saving(ProductVariant $variant): void
    {
        $product = $variant->product;
        $productName = $product ? $product->name : '';
        $productSlug = $product ? Str::slug($product->name) : '';

        if (filled($variant->name)) {
            $variant->name = trim($variant->name);
        } else {
            $productTitle = $product ? Str::title($product->name) : '';
            $displayLabel = $variant->swatch_label
                ?? collect($variant->option_values ?? [])
                    ->map(fn ($v) => Str::title($v))
                    ->implode(' ');

            if ($displayLabel) {
                $variant->name = $productTitle.' '.$displayLabel;
            } elseif ($productTitle) {
                $variant->name = $productTitle;
            }
        }

        // Always ensure the slug includes the product prefix for uniqueness and SEO
        $variantPart = $variant->name ? Str::slug($variant->name) : '';
        
        if ($productSlug && $variantPart && $productSlug !== $variantPart) {
            $variant->slug = $productSlug . '-' . $variantPart;
        } else {
            $variant->slug = $productSlug ?: $variantPart;
        }
    }

    public function forceDeleting(ProductVariant $variant): void
    {
        $variant->clearMediaCollection('primary_image');
        $variant->clearMediaCollection('hover_image');
        $variant->clearMediaCollection('gallery');
        $variant->clearMediaCollection('dimension_image');
        $variant->clearMediaCollection('swatch_image');
    }
}

<?php

namespace App\Observers;

use App\Models\Product\ProductVariant;
use Illuminate\Support\Str;

class ProductVariantObserver
{
    public function saving(ProductVariant $variant): void
    {
        if (filled($variant->name)) {
            $variant->name = trim($variant->name);
            $variant->slug = Str::slug($variant->name);
        } else {
            $product = $variant->product;
            $productName = $product ? Str::title($product->name) : '';
            $optionLabels = collect($variant->option_values ?? [])
                ->map(fn ($v) => Str::title($v))
                ->implode(' ');

            if ($optionLabels) {
                $variant->name = $productName.' '.$optionLabels;
            } elseif ($productName) {
                $variant->name = $productName;
            }
            $variant->slug = Str::slug($variant->name);
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

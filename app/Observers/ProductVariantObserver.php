<?php

namespace App\Observers;

use App\Models\Product\ProductVariant;
use Illuminate\Support\Str;

class ProductVariantObserver
{
    public function saving(ProductVariant $variant): void
    {
        $product = $variant->product;
        $productName = $product ? Str::title($product->name) : '';

        if (filled($variant->title)) {
            $title = trim($variant->title);
            if ($productName && ! str_starts_with(strtolower($title), strtolower($productName))) {
                $title = $productName.' '.$title;
            }
            $variant->title = $title;
            $variant->slug = Str::slug($variant->title);
        } elseif ($productName) {
            $optionLabels = collect($variant->option_values ?? [])
                ->map(fn ($v) => Str::title($v))
                ->implode(' ');
            $variant->title = $optionLabels
                ? $productName.' '.$optionLabels
                : $productName;
            $variant->slug = Str::slug($variant->title);
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

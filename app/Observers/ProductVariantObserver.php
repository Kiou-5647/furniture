<?php

namespace App\Observers;

use App\Models\Product\ProductCard;
use App\Models\Product\ProductVariant;
use App\Models\Setting\Lookup;
use Illuminate\Support\Str;

class ProductVariantObserver
{
    public function saving(ProductVariant $variant): void
    {
        $product = $variant->product;
        $productSlug = $product ? Str::slug($product->name) : '';

        if (filled($variant->name)) {
            $variant->name = trim($variant->name);
        } else {
            $optionValues = $variant->option_values ?? [];
            $displayLabel = $variant->swatch_label
                ?? collect($optionValues)
                ->map(function ($valSlug, $nsSlug) {
                    return Lookup::whereHas('namespace', fn($q) => $q->where('slug', $nsSlug))
                        ->where('slug', $valSlug)
                        ->value('display_name') ?? Str::title($valSlug);
                })
                ->filter()
                ->implode(' ');

            $variant->name = $displayLabel ?: ($product ? $product->name : '');
        }

        $variantPart = $variant->name ? Str::slug($variant->name) : '';
        $variant->slug = ($productSlug && $variantPart && $productSlug !== $variantPart)
            ? $productSlug . '-' . $variantPart
            : ($productSlug ?: $variantPart);

        // --- NEW: Automatic Card Assignment ---
        if ($product) {
            $this->assignProductCard($variant, $product);
        }
    }

    public function updated(ProductVariant $variant): void
    {
        // Only trigger sync if metrics changed
        if ($variant->wasChanged(['views_count', 'reviews_count', 'average_rating'])) {
            if ($variant->productCard) {
                $variant->productCard->syncMetricsFromVariants();
            }

            if ($variant->product) {
                $variant->product->syncMetricsFromVariants();
            }
        }
    }

    public function deleted(ProductVariant $variant): void
    {
        // Cleanup: If this was the last variant in the card, delete the card
        if ($variant->productCard && $variant->productCard->variants()->count() === 0) {
            $variant->productCard->delete();
        }
    }

    protected function assignProductCard(ProductVariant $variant, $product): void
    {
        $product->refresh();

        $groups = $product->option_groups ?? [];
        if (!is_array($groups)) {
            $groups = json_decode($groups, true) ?? [];
        }

        $nonSwatchNamespaces = collect($groups)
            ->filter(fn($g) => !($g['is_swatches'] ?? false))
            ->pluck('namespace')
            ->toArray();

        // 1. Get values, sort them, and FORCE to a re-indexed array
        $cardCombo = collect($variant->option_values)
            ->only($nonSwatchNamespaces)
            ->sort()
            ->values()
            ->toArray();

        if (empty($cardCombo)) {
            $card = ProductCard::firstOrCreate(['product_id' => $product->id]);
        } else {
            $cards = ProductCard::where('product_id', $product->id)
                ->with('options')
                ->get();

            $card = $cards->first(function ($existingCard) use ($cardCombo) {
                // 2. Get existing options, sort them, and FORCE to a re-indexed array
                $existingOptions = $existingCard->options->pluck('slug')
                    ->sort()
                    ->values()
                    ->toArray();

                // 3. Strict comparison of re-indexed arrays
                return $existingOptions === $cardCombo;
            });

            if (!$card) {
                $card = ProductCard::create(['product_id' => $product->id]);

                $lookupIds = [];
                foreach ($cardCombo as $val) {
                    $lookup = \App\Models\Setting\Lookup::where('slug', $val)->first();
                    if ($lookup) $lookupIds[] = $lookup->id;
                }
                $card->options()->sync($lookupIds);
            }
        }

        $variant->product_card_id = $card->id;
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

<?php

namespace App\Services\Public;

use App\Models\Product\ProductVariant;

class ProductDiscoveryService
{
    public function searchVariants(string $queryStr, int $limit = 5): array
    {
        $queryStr = trim($queryStr);
        if (strlen($queryStr) < 2) {
            return ['results' => collect(), 'total' => 0];
        }

        $words = preg_split('/\s+/', $queryStr, -1, PREG_SPLIT_NO_EMPTY);

        $query = ProductVariant::query()
            ->select('product_variants.*')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('collections', 'products.collection_id', '=', 'collections.id')
            ->where('products.status', 'published')
            ->where('product_variants.status', 'active');

        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                // \y matches word boundaries in PostgreSQL
                $regex = "\\y" . $word . "\\y";

                $q->where(function ($sq) use ($regex) {
                    // Use whereRaw with bindings for security and correctness
                    $sq->whereRaw("products.name ~* ?", [$regex])
                        ->orWhereRaw("categories.display_name ~* ?", [$regex])
                        ->orWhereRaw("collections.display_name ~* ?", [$regex])
                        ->orWhereRaw("product_variants.name ~* ?", [$regex])
                        ->orWhereRaw("product_variants.sku ~* ?", [$regex])
                        ->orWhereRaw("product_variants.description ~* ?", [$regex])
                        ->orWhereRaw("products.features::text ~* ?", [$regex])
                        ->orWhereRaw("products.specifications::text ~* ?", [$regex])
                        ->orWhereRaw("product_variants.features::text ~* ?", [$regex])
                        ->orWhereRaw("product_variants.specifications::text ~* ?", [$regex]);
                });
            }
        });

        $total = $query->count();

        $results = $query->orderByDesc('product_variants.sales_count')
            ->limit($limit)
            ->with(['product'])
            ->get()
            ->map(fn($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'slug' => $variant->slug,
                'full_name' => "{$variant->product->name} - {$variant->name}",
                'image' => $variant->getFirstMediaUrl('primary_image', 'webp'),
                'price' => $variant->price,
                'effective_price' => $variant->getEffectivePrice(),
                'on_sale' => $variant->getEffectivePrice() < $variant->price,
            ]);

        return [
            'results' => $results,
            'total' => $total,
        ];
    }
}

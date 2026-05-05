<?php

namespace App\Http\Controllers\Public\Product;

use App\Http\Resources\Public\Product\ProductReviewResource;
use App\Models\Customer\Review;
use App\Models\Product\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductReviewController
{
    /**
     * Get paginated reviews for a specific product.
     */
    public function index(string $sku, Request $request): JsonResponse
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();
        $product = $variant->product;

        $query = Review::query()
            ->where('is_published', true)
            ->with(['customer', 'variant.product']);

        // Scope: All Product Variants vs Only Current Variant
        if ($request->query('scope') === 'variant') {
            $query->where('variant_id', $variant->id);
        } else {
            $query->whereHas('variant', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            });
        }

        // Filter by Rating
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->query('rating'));
        }

        // Filter by Specific Variant ID
        if ($request->filled('variant_id')) {
            $query->where('variant_id', $request->query('variant_id'));
        }

        $reviews = $query->latest('updated_at')->paginate(12);

        return response()->json([
            'data' => ProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'average_rating' => (float) $product->average_rating,
                'reviews_count' => (int) $product->reviews_count,
            ],
        ]);
    }
}

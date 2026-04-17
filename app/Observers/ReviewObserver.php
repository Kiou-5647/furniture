<?php

namespace App\Observers;

use App\Models\Customer\Review;
use App\Models\Product\ProductVariant;
use Illuminate\Support\Facades\DB;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->syncStats($review);
    }

    public function updated(Review $review): void
    {
        $this->syncStats($review);
    }

    public function deleted(Review $review): void
    {
        $this->syncStats($review);
    }

    protected function syncStats(Review $review): void
    {
        $reviewable = $review->reviewable;
        if (!$reviewable) return;

        DB::transaction(function () use ($reviewable) {
            // 1. Update the immediate target (Variant or Bundle)
            $stats = Review::where('reviewable_id', $reviewable->id)
                ->where('reviewable_type', get_class($reviewable))
                ->where('is_published', true)
                ->selectRaw('COUNT(*) as count, AVG(rating) as avg')
                ->first();

            $reviewable->update([
                'reviews_count' => $stats->count ?? 0,
                'average_rating' => $stats->avg ?? 0,
            ]);

            if ($reviewable instanceof ProductVariant) {
                $product = $reviewable->product;

                $variantIds = $product->variants()->pluck('id');

                $productStats = Review::whereIn('reviewable_id', $variantIds)
                    ->where('reviewable_type', ProductVariant::class)
                    ->where('is_published', true)
                    ->selectRaw('COUNT(*) as count, AVG(rating) as avg')
                    ->first();

                $product->update([
                    'reviews_count' => $productStats->count ?? 0,
                    'average_rating' => $productStats->avg ?? 0,
                ]);
            }
        });
    }
}

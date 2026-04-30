<?php

namespace App\Observers;

use App\Models\Customer\Review;
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
        $variant = $review->variant; // Use the new variant relationship
        if (!$variant) return;

        DB::transaction(function () use ($variant) {
            $stats = Review::where('variant_id', $variant->id)
                ->where('is_published', true)
                ->selectRaw('COUNT(*) as count, AVG(rating) as avg')
                ->first();

            $variant->update([
                'reviews_count' => $stats->count ?? 0,
                'average_rating' => $stats->avg ?? 0,
            ]);
        });
    }
}

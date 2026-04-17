<?php

namespace App\Actions\Customer;

use App\Models\Customer\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReviewAction
{
    /**
     * Create a new review for a reviewable entity.
     * 
     * @param array $data [
     *     'reviewable_id' => string,
     *     'reviewable_type' => string,
     *     'customer_id' => string,
     *     'rating' => int,
     *     'comment' => ?string,
     * ]
     */
    public function execute(array $data): Review
    {
        return DB::transaction(function () use ($data) {
            // 1. Ensure the customer hasn't already reviewed this specific entity
            $exists = Review::where('reviewable_id', $data['reviewable_id'])
                ->where('reviewable_type', $data['reviewable_type'])
                ->where('customer_id', $data['customer_id'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'review' => 'You have already reviewed this item.',
                ]);
            }

            // 2. Create the review
            return Review::create([
                'reviewable_id' => $data['reviewable_id'],
                'reviewable_type' => $data['reviewable_type'],
                'customer_id' => $data['customer_id'],
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_published' => true, // Default to published; could be configurable
            ]);
        });
    }
}

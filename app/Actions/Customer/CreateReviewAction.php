<?php

namespace App\Actions\Customer;

use App\Models\Customer\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReviewAction
{
    public function execute(array $data): Review
    {
        return DB::transaction(function () use ($data) {
            $exists = Review::where('variant_id', $data['variant_id'])
                ->where('customer_id', $data['customer_id'])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'review' => 'Bạn đã đánh giá sản phẩm này rồi!',
                ]);
            }

            // 2. Create the review
            return Review::create([
                'variant_id' => $data['variant_id'],
                'customer_id' => $data['customer_id'],
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_published' => $data['is_published'],
            ]);
        });
    }
}

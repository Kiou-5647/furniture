<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Customer\CreateReviewAction;
use App\Http\Requests\Customer\StoreReviewRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController
{
    public function __construct(
        protected CreateReviewAction $createReviewAction
    ) {}

    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            $review = $this->createReviewAction->execute([
                ...$request->validated(),
                'customer_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Review submitted successfully.',
                'review' => $review,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while submitting your review.'], 500);
        }
    }
}

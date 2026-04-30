<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Customer\CreateReviewAction;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Models\Customer\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController
{
    public function __construct(
        protected CreateReviewAction $createReviewAction
    ) {}

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        try {
            $this->createReviewAction->execute([
                ...$request->validated(),
                'customer_id' => Auth::user()->customer->id,
            ]);

            return redirect()->back()->with('success', 'Đã tạo đánh giá sản phẩm thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra trong quá trình xử lý.');
        }
    }

    public function update(StoreReviewRequest $request, Review $review): RedirectResponse
    {
        // Ensure the review belongs to the authenticated user
        if ($review->customer_id !== Auth::user()->customer->id) {
            abort(403);
        }

        // If it's already published, it's uneditable
        if ($review->is_published) {
            return redirect()->back()->with('error', 'Đánh giá đã đăng không thể bị chỉnh sửa');
        }

        $review->update($request->validated());

        return redirect()->back()->with('success', 'Đã lưu đánh giá sản phẩm.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        if ($review->customer_id !== Auth::user()->customer->id) {
            abort(403);
        }

        // Only allow deletion of drafts
        if ($review->is_published) {
            return redirect()->back()->with('error', 'Không thể xóa đánh giá đã đăng');
        }

        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá sản phẩm.');
    }
}

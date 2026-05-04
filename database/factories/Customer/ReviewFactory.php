<?php

namespace Database\Factories\Customer;

use App\Models\Customer\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $comments = [
            'Sản phẩm tuyệt vời, giao hàng nhanh!',
            'Chất lượng tốt, đúng như mô tả.',
            'Rất hài lòng với sản phẩm này.',
            'Thiết kế đẹp, màu sắc trang nhã.',
            'Sản phẩm ổn trong tầm giá.',
            'Giao hàng hơi chậm nhưng sản phẩm rất tốt.',
            'Đóng gói cẩn thận, sản phẩm chắc chắn.',
            'Sử dụng một thời gian thấy rất bền.',
            'Màu sắc hơi khác ảnh một chút nhưng vẫn đẹp.',
            'Hỗ trợ khách hàng rất nhiệt tình.',
            'Sản phẩm tinh tế, phù hợp với không gian nhà tôi.',
            'Chất liệu cao cấp, cảm giác rất sang trọng.',
            'Lắp đặt dễ dàng, hướng dẫn chi tiết.',
            'Giá cả hợp lý cho một sản phẩm chất lượng như vậy.',
            'Sẽ tiếp tục ủng hộ shop trong tương lai.',
            'Sản phẩm đúng kích thước, vừa vặn với căn phòng.',
            'Độ hoàn thiện cao, không có chi tiết thừa.',
            'Sản phẩm chắc chắn, không bị rung lắc.',
            'Mẫu mã đa dạng, dễ lựa chọn.',
            'Rất thích cách shop chăm sóc khách hàng.',
        ];

        return [
            'rating' => $this->faker->numberBetween(3, 5),
            'comment' => $this->faker->randomElement($comments),
            'is_published' => true,
        ];
    }
}

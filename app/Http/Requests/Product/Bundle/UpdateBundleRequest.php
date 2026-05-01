<?php

namespace App\Http\Requests\Product\Bundle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý gói sản phẩm');
    }

    public function rules(): array
    {
        $bundle = $this->route('bundle');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('bundles')->ignore($bundle->id)],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount', 'fixed_price'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'contents' => ['required', 'array', 'min:1'],
            'contents.*.product_card_id' => ['required', 'exists:product_cards,id'],
            'contents.*.quantity' => ['required', 'integer', 'min:1'],

            'primary_image_file' => ['nullable', 'image', 'max:5120'],
            'hover_image_file' => ['nullable', 'image', 'max:5120'],
            'gallery_files' => ['nullable', 'array', 'max:10'],
            'gallery_files.*' => ['image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên gói sản phẩm.',
            'name.max' => 'Tên gói sản phẩm không được vượt quá 255 ký tự.',
            'slug.required' => 'Vui lòng nhập slug cho gói sản phẩm.',
            'slug.unique' => 'Slug này đã được sử dụng.',
            'discount_type.required' => 'Vui lòng chọn loại giảm giá.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
            'discount_value.required' => 'Vui lòng nhập giá trị giảm giá.',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0.',
            'contents.required' => 'Gói sản phẩm phải chứa ít nhất một sản phẩm.',
            'contents.min' => 'Gói sản phẩm phải chứa ít nhất một sản phẩm.',
            'contents.*.product_card_id.required' => 'Vui lòng chọn sản phẩm cho mục này.',
            'contents.*.product_card_id.exists' => 'Sản phẩm được chọn không tồn tại.',
            'contents.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'contents.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            'primary_image.image' => 'Ảnh chính phải là hình ảnh.',
            'primary_image.max' => 'Dung lượng ảnh chính không được vượt quá 5MB.',
            'hover_image.image' => 'Ảnh hover phải là hình ảnh.',
            'hover_image.max' => 'Dung lượng ảnh hover không được vượt quá 5MB.',
            'gallery.*.image' => 'Tệp tải lên trong thư viện phải là hình ảnh.',
            'gallery.*.max' => 'Dung lượng hình ảnh không được vượt quá 5MB.',
            'gallery.max' => 'Thư viện ảnh không được vượt quá 10 hình.',
        ];
    }
}

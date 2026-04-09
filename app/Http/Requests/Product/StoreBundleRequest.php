<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bundles.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('bundles', 'slug')],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount', 'fixed_price'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'contents' => ['required', 'array', 'min:1'],
            'contents.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'contents.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên gói sản phẩm.',
            'name.max' => 'Tên gói sản phẩm không được vượt quá 255 ký tự.',
            'slug.required' => 'Vui lòng nhập slug.',
            'slug.unique' => 'Slug đã được sử dụng.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
            'discount_value.numeric' => 'Giá trị giảm giá phải là số.',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn hoặc bằng 0.',
            'contents.required' => 'Gói sản phẩm phải có ít nhất một sản phẩm.',
            'contents.min' => 'Gói sản phẩm phải có ít nhất một sản phẩm.',
            'contents.*.product_id.required' => 'Vui lòng chọn sản phẩm.',
            'contents.*.product_id.exists' => 'Sản phẩm đã chọn không hợp lệ.',
            'contents.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'contents.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
        ];
    }
}

<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bundles.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('bundles', 'slug')->ignore($this->bundle->id)],
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
            'slug.unique' => 'Slug đã được sử dụng.',
            'contents.min' => 'Gói sản phẩm phải có ít nhất một sản phẩm.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
        ];
    }
}

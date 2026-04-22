<?php

namespace App\Http\Requests\Public\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchasable_type' => ['required', Rule::in([
                'App\\Models\\Product\\ProductVariant',
                'App\\Models\\Product\\Bundle',
            ])],
            'purchasable_id' => ['required', 'string', 'morph_exists:purchasable_type'],
            'quantity' => ['required', 'integer', 'min:1'],
            'configuration' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchasable_type.required' => 'Vui lòng chọn loại sản phẩm.',
            'purchasable_type.in' => 'Loại sản phẩm không hợp lệ.',
            'purchasable_id.required' => 'Vui lòng chọn sản phẩm.',
            'purchasable_id.uuid' => 'Mã sản phẩm không hợp lệ.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
        ];
    }
}

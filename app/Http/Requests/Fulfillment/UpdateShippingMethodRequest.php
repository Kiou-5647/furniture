<?php

namespace App\Http\Requests\Fulfillment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('shipping_methods.manage');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('shipping_methods', 'code')->ignore($this->shippingMethod->id)],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_days' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã phương thức là bắt buộc.',
            'code.unique' => 'Mã phương thức đã tồn tại.',
            'name.required' => 'Tên phương thức là bắt buộc.',
            'price.required' => 'Giá là bắt buộc.',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0.',
        ];
    }
}

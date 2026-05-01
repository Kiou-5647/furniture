<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StockOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Xem đơn hàng');
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'variant_id.required' => 'Mã biến thể là bắt buộc.',
        ];
    }
}

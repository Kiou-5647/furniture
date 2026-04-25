<?php

namespace App\Http\Requests\Fulfillment;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('orders.manage');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.order_item_id' => ['required', 'uuid'],
            'items.*.location_id' => ['required', 'uuid', 'exists:locations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.variant_id' => ['required', 'uuid', 'exists:product_variants,id']
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Danh sách mục là bắt buộc.',
            'items.*.order_item_id.required' => 'Mã mục đơn hàng là bắt buộc.',
            'items.*.location_id.required' => 'Mã kho là bắt buộc.',
            'items.*.location_id.exists' => 'Kho không tồn tại.',
            'items.*.quantity.required' => 'Số lượng là bắt buộc.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
        ];
    }
}

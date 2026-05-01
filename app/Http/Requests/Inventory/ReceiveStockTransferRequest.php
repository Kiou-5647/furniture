<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý kho hàng');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'uuid', 'exists:stock_transfer_items,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Vui lòng nhập thông tin nhận hàng.',
            'items.*.item_id.required' => 'Mã sản phẩm chuyển kho là bắt buộc.',
            'items.*.item_id.exists' => 'Sản phẩm chuyển kho không tồn tại.',
            'items.*.quantity_received.required' => 'Vui lòng nhập số lượng nhận.',
            'items.*.quantity_received.min' => 'Số lượng nhận không được âm.',
        ];
    }
}

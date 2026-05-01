<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý kho hàng');
    }

    public function rules(): array
    {
        return [
            'from_location_id' => ['required', 'uuid', 'exists:locations,id'],
            'to_location_id' => ['required', 'uuid', 'exists:locations,id', 'different:from_location_id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_location_id.required' => 'Vui lòng chọn vị trí nguồn.',
            'from_location_id.exists' => 'Vị trí nguồn không tồn tại.',
            'to_location_id.required' => 'Vui lòng chọn vị trí đích.',
            'to_location_id.exists' => 'Vị trí đích không tồn tại.',
            'to_location_id.different' => 'Vị trí đích phải khác vị trí nguồn.',
            'items.required' => 'Phiếu chuyển kho phải có ít nhất một sản phẩm.',
            'items.min' => 'Phiếu chuyển kho phải có ít nhất một sản phẩm.',
            'items.*.variant_id.required' => 'Vui lòng chọn sản phẩm.',
            'items.*.variant_id.exists' => 'Sản phẩm không tồn tại.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.min' => 'Số lượng phải ít nhất là 1.',
        ];
    }
}

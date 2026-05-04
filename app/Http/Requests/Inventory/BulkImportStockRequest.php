<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class BulkImportStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => 'required|exists:locations,id',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_per_unit' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Vui lòng nhập ít nhất một sản phẩm.',
            'items.*.variant_id.required' => 'Vui lòng chọn sản phẩm.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
            'items.*.cost_per_unit.min' => 'Giá vốn không được âm.',
        ];
    }
}

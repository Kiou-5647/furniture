<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý kho hàng');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['warehouse', 'retail', 'vendor'])],
            'street' => ['nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'max:20'],
            'province_name' => ['nullable', 'string', 'max:100'],
            'ward_code' => ['nullable', 'string', 'max:20'],
            'ward_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'manager_id' => ['nullable', 'uuid'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên vị trí.',
            'type.required' => 'Vui lòng chọn loại vị trí.',
            'type.in' => 'Loại vị trí không hợp lệ.',
            'manager_id.exists' => 'Người quản lý đã chọn không hợp lệ.',
        ];
    }
}

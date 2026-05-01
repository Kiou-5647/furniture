<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý phòng ban');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('departments', 'code')],
            'description' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên phòng ban.',
            'code.required' => 'Vui lòng nhập mã phòng ban.',
            'code.unique' => 'Mã phòng ban đã được sử dụng.',
            'manager_id.exists' => 'Quản lý đã chọn không hợp lệ.',
        ];
    }
}

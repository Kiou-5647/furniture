<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý nhân viên');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'store_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'warehouse_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'hire_date' => ['nullable', 'date'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên đăng nhập.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'department_id.exists' => 'Phòng ban đã chọn không hợp lệ.',
        ];
    }
}

<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDesignerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('designers.manage');
    }

    public function rules(): array
    {
        $isEmployee = $this->filled('employee_id');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => $isEmployee
                ? ['nullable', 'email', 'max:255']
                : ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20'],
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'bio' => ['nullable', 'string'],
            'portfolio_url' => ['nullable', 'url'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'auto_confirm_bookings' => ['boolean'],
            'is_active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:10240'],
            'availabilities' => ['nullable', 'array'],
            'availabilities.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'availabilities.*.hour' => ['required', 'integer', 'between:0,23'],
            'availabilities.*.is_available' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng.',
            'hourly_rate.required' => 'Vui lòng nhập giá theo giờ.',
            'hourly_rate.numeric' => 'Giá theo giờ phải là số.',
            'hourly_rate.min' => 'Giá theo giờ phải lớn hơn hoặc bằng 0.',
        ];
    }
}

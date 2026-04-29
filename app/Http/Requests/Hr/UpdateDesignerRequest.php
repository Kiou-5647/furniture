<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('designers.manage');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
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
            'hourly_rate.required' => 'Vui lòng nhập giờ làm.',
            'hourly_rate.numeric' => 'Giờ làm phải là số.',
            'hourly_rate.min' => 'Giờ làm phải lớn hơn hoặc bằng 0.',
        ];
    }
}

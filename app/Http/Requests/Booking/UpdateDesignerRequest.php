<?php

namespace App\Http\Requests\Booking;

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
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'vendor_user_id' => ['nullable', 'uuid', 'exists:vendor_users,id'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'auto_confirm_bookings' => ['boolean'],
            'is_active' => ['boolean'],
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

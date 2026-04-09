<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDesignServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('design_services.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['consultation', 'custom_build'])],
            'is_schedule_blocking' => ['boolean'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'deposit_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên dịch vụ.',
            'type.required' => 'Vui lòng chọn loại dịch vụ.',
            'base_price.required' => 'Vui lòng nhập giá cơ bản.',
            'deposit_percentage.required' => 'Vui lòng nhập phần trăm đặt cọc.',
            'deposit_percentage.max' => 'Phần trăm đặt cọc không được vượt quá 100.',
        ];
    }
}

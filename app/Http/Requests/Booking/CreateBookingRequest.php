<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bookings.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid', 'exists:users,id'],
            'designer_id' => ['required', 'uuid', 'exists:designers,id'],
            'service_id' => ['required', 'uuid', 'exists:design_services,id'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'deadline_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'designer_id.required' => 'Vui lòng chọn nhà thiết kế.',
            'service_id.required' => 'Vui lòng chọn dịch vụ.',
            'end_at.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ];
    }
}

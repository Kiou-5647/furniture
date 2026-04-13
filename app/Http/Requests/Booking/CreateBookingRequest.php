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
            'customer_id' => ['nullable', 'uuid', 'exists:users,id'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:100'],
            'customer_email' => ['required_without:customer_id', 'nullable', 'email', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'designer_id' => ['required', 'uuid', 'exists:designers,id'],
            'service_id' => ['required', 'uuid', 'exists:design_services,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'end_hour' => ['required', 'integer', 'min:1', 'max:24', 'gt:start_hour'],
            'deadline_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required_without' => 'Vui lòng nhập tên khách hàng.',
            'customer_email.required_without' => 'Vui lòng nhập email khách hàng.',
            'designer_id.required' => 'Vui lòng chọn nhà thiết kế.',
            'designer_id.uuid' => 'Vui lòng chọn nhà thiết kế.',
            'service_id.required' => 'Vui lòng chọn dịch vụ.',
            'service_id.uuid' => 'Vui lòng chọn dịch vụ.',
            'end_hour.gt' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->customer_id && ! $this->customer_name) {
                $validator->errors()->add('customer_id', 'Vui lòng chọn khách hàng hoặc nhập thông tin khách vãng lai.');
                $validator->errors()->add('customer_name', 'Vui lòng chọn khách hàng hoặc nhập thông tin khách vãng lai.');
            }
        });
    }
}

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
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'customer_email' => ['required_without:customer_id', 'nullable', 'email', 'max:255'],
            'customer_phone' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],

            'designer_id' => ['required', 'uuid', 'exists:designers,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'province_code' => ['required_without:customer_id', 'nullable', 'string', 'max:10'],
            'ward_code' => ['required_without:customer_id', 'nullable', 'string', 'max:10'],
            'province_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'ward_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'street' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required_without' => 'Vui lòng nhập tên khách hàng hoặc chọn khách hàng có sẵn.',
            'customer_email.required_without' => 'Vui lòng nhập email khách hàng.',
            'customer_phone.required_without' => 'Vui lòng nhập số điện thoại khách hàng.',
            'street.required_without' => 'Vui lòng nhập địa chỉ.',
            'designer_id.required' => 'Vui lòng chọn nhà thiết kế.',
            'date.required' => 'Vui lòng chọn ngày.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'duration.required' => 'Vui lòng chọn thời lượng.',
            'duration.min' => 'Thời lượng tối thiểu là 1 giờ.',
        ];
    }
}

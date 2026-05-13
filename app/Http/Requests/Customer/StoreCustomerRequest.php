<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'size:2', 'exists:provinces,province_code'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'ward_code' => ['nullable', 'string', 'size:5', 'exists:wards,ward_code'],
            'ward_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'province_code.exists' => 'Tỉnh/Thành phố không hợp lệ.',
            'ward_code.exists' => 'Phường/Xã không hợp lệ.',
        ];
    }
}

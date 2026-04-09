<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payments.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'uuid', 'exists:users,id'],
            'gateway' => ['required', 'string', 'max:255'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['required', 'uuid', 'exists:invoices,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0'],
            'gateway_payload' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'gateway.required' => 'Vui lòng chọn cổng thanh toán.',
            'transaction_id.required' => 'Vui lòng nhập mã giao dịch.',
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.min' => 'Số tiền phải lớn hơn hoặc bằng 0.',
            'allocations.required' => 'Vui lòng chọn hóa đơn cần thanh toán.',
            'allocations.min' => 'Phải có ít nhất một hóa đơn.',
            'allocations.*.invoice_id.required' => 'Vui lòng chọn hóa đơn.',
            'allocations.*.amount.required' => 'Vui lòng nhập số tiền thanh toán.',
            'allocations.*.amount.min' => 'Số tiền phải lớn hơn hoặc bằng 0.',
        ];
    }
}

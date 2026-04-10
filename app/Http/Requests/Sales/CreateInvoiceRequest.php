<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('invoices.create');
    }

    public function rules(): array
    {
        return [
            'invoiceable_type' => ['required', Rule::in([
                'App\\Models\\Sales\\Order',
                'App\\Models\\Design\\Booking',
            ])],
            'invoiceable_id' => ['required', 'uuid'],
            'type' => ['required', Rule::in(['deposit', 'final_balance', 'full'])],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'validated_by' => ['nullable', 'uuid', 'exists:employees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'invoiceable_type.required' => 'Vui lòng chọn loại đối tượng.',
            'invoiceable_type.in' => 'Loại đối tượng không hợp lệ.',
            'invoiceable_id.required' => 'Vui lòng chọn đối tượng.',
            'type.required' => 'Vui lòng chọn loại hóa đơn.',
            'type.in' => 'Loại hóa đơn không hợp lệ.',
            'amount_due.required' => 'Vui lòng nhập số tiền.',
            'amount_due.min' => 'Số tiền phải lớn hơn hoặc bằng 0.',
        ];
    }
}

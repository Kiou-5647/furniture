<?php

namespace App\Http\Requests\Sales;

use App\Models\Customer\CustomerAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('orders.create');
    }

    public function rules(): array
    {
        $rules = [
            'customer_id' => ['nullable', 'uuid', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchasable_type' => ['required', Rule::in([
                'App\\Models\\Product\\ProductVariant',
                'App\\Models\\Product\\Bundle',
            ])],
            'items.*.purchasable_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.configuration' => ['nullable', 'array'],
            'items.*.source_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'source' => ['nullable', Rule::in(['in_store', 'online'])],
            'store_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'shipping_method_id' => ['nullable', 'uuid', 'exists:shipping_methods,id'],
            'payment_method' => ['nullable', Rule::in(['cash', 'bank_transfer', 'cod'])],
            'province_code' => ['nullable', 'string', 'max:10'],
            'ward_code' => ['nullable', 'string', 'max:10'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'ward_name' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:255'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'shipping_address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
            'items.min' => 'Đơn hàng phải có ít nhất một sản phẩm.',
            'items.*.purchasable_type.required' => 'Vui lòng chọn loại sản phẩm.',
            'items.*.purchasable_id.required' => 'Vui lòng chọn sản phẩm.',
            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            'items.*.unit_price.required' => 'Vui lòng nhập đơn giá.',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn hoặc bằng 0.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customerId = $this->input('customer_id');
            $addressId = $this->input('shipping_address_id');

            if ($customerId && $addressId) {
                $exists = CustomerAddress::where('customer_id', $customerId)
                    ->where('id', $addressId)
                    ->exists();

                if (! $exists) {
                    $validator->errors()->add(
                        'shipping_address_id',
                        'Địa chỉ không thuộc khách hàng này.'
                    );
                }
            }
        });
    }
}

<?php

namespace App\Http\Requests\Sales;

use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý khuyến mãi');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['percentage', 'fixed_amount'])],
            'value' => ['required', 'numeric', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'is_active' => ['boolean'],
            'discountable_type' => ['nullable', Rule::in([
                Product::class,
                ProductVariant::class,
                Category::class,
                Collection::class,
                Vendor::class,
            ])],
            'discountable_id' => ['nullable', 'morph_exists:discountable_type'],
        ];
    }
}

<?php

namespace App\Http\Requests\Sales;

use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Vendor\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('discounts.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['percentage', 'fixed_amount'])],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'is_active' => ['boolean'],
            'discountable_type' => ['nullable', Rule::in([
                Category::class,
                Collection::class,
                Vendor::class,
            ])],
            'discountable_id' => ['nullable', 'morph_exists:discountable_type'],
        ];
    }
}

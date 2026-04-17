<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reviewable_id' => ['required', 'string', 'uuid'],
            'reviewable_type' => ['required', 'string', Rule::in([
                \App\Models\Product\ProductVariant::class,
                \App\Models\Product\Bundle::class,
            ])],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

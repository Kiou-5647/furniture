<?php

namespace App\Http\Requests\Customer;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (Auth::user()->type !== UserType::Customer)
            return false;
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'string', 'uuid', 'exists:product_variants,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'is_published' => ['boolean'],
        ];
    }
}

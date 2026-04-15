<?php

namespace App\Http\Requests\Fulfillment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentItemLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_location_id' => ['required', 'uuid', 'exists:locations,id'],
        ];
    }
}

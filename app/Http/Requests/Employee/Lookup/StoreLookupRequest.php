<?php

namespace App\Http\Requests\Employee\Lookup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLookupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('lookup.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'namespace' => [
                'required', 'string', 'max:64', 'exists:lookups,namespace',
            ],
            'key' => [
                'required', 'string', 'max:64',
                Rule::unique('lookups')->where(fn ($q) => $q->where('namespace', $this->input('namespace'))),
            ],
            'display_name' => [
                'required', 'string', 'max:255',
            ],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'namespace.required' => 'Vui lòng chọn namespace.',
            'namespace.in' => 'Namespace đã chọn không tồn tại.',
            'key.unique' => 'Khóa đã tồn tại trong namespace được chọn.',
        ];
    }
}

<?php

namespace App\Http\Requests\Employee\Lookup;

use App\Enums\LookupType;
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
            'namespace' => ['required', Rule::enum(LookupType::class)],
            'key' => [
                'required', 'string', 'max:64',
                Rule::unique('lookups')->where(fn ($q) => $q->where('namespace', $this->input('namespace'))),
            ],
            'display_name' => [
                'required', 'string', 'max:255',
            ],
            'metadata' => ['nullable', 'array'],
            'metadata.hex_code' => [
                Rule::requiredIf($this->input('namespace') === LookupType::Colors->value),
                'nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'metadata.image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'namespace.required' => 'Vui lòng chọn namespace.',
            'namespace.in' => 'Namespace đã chọn không tồn tại.',
            'key.unique' => 'Khóa đã tồn tại trong namespace được chọn.',
            'metadata.hex_code.required' => 'Vui lòng cung cấp mã màu HEX.',
            'metadata.hex_code.regex' => 'Định dạng mã màu không hợp lệ (VD: #FFFFFF).',
        ];
    }
}

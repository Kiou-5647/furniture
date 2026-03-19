<?php

namespace App\Http\Requests\Employee\Lookup;

use App\Enums\LookupType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateLookupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $lookup = $this->route('lookup');

        return $this->user()->can('lookup.update', $lookup);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $lookup = $this->route('lookup');
        $lookupId = $lookup->id;
        $namespace = $this->input('namespace');

        Log::info('Start valicating');

        return [
            'namespace' => ['required', Rule::enum(LookupType::class)],
            'key' => [
                'required', 'string', 'max:64',
                Rule::unique('lookups')
                    ->ignore($lookupId)
                    ->where(fn ($q) => $q->where('namespace', $namespace)),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'metadata.hex_code' => [
                Rule::requiredIf($namespace === LookupType::Colors->value),
                'nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'metadata.image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'namespace.enum' => 'Danh mục không hợp lệ.',
            'key.unique' => 'Khóa này đã tồn tại trong danh mục.',
            'metadata.hex_code.required' => 'Vui lòng cung cấp mã màu HEX.',
            'metadata.hex_code.regex' => 'Định dạng mã màu không hợp lệ (VD: #FFFFFF).',
        ];
    }
}

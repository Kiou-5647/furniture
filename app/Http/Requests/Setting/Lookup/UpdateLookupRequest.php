<?php

namespace App\Http\Requests\Setting\Lookup;

use App\Enums\LookupType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLookupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('lookups.manage');
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

        return [
            'namespace' => ['required', Rule::enum(LookupType::class)],
            'slug' => [
                'required', 'string', 'max:64',
                Rule::unique('lookups')->ignore($lookupId)->where(fn ($q) => $q->where('namespace', $namespace)),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'metadata' => ['nullable', 'array'],
            'metadata.hex_code' => [
                Rule::requiredIf($namespace === LookupType::Colors->value),
                'nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'metadata.meta_title' => ['nullable', 'string', 'max:255'],
            'metadata.meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'namespace.enum' => 'Danh mục đã chọn không tồn tại.',
            'slug.unique' => 'Khóa này đã tồn tại trong danh mục.',
            'metadata.hex_code.required' => 'Vui lòng cung cấp mã màu HEX.',
            'metadata.hex_code.regex' => 'Định dạng mã màu không hợp lệ (VD: #FFFFFF).',
            'image_path.image' => 'Hình ảnh không hợp lệ.',
        ];
    }
}

<?php

namespace App\Http\Requests\Setting\LookupNamespace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateLookupNamespaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('lookups.manage');
    }

    public function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?? $this->input('display_name')),
        ]);
    }

    public function rules(): array
    {
        $namespace = $this->route('lookupNamespace');

        return [
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('lookup_namespaces')->ignore($namespace->id),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'for_variants' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Khóa đã tồn tại.',
            'display_name.required' => 'Vui lòng nhập tên hiển thị.',
        ];
    }
}

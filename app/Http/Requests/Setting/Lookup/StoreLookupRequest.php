<?php

namespace App\Http\Requests\Setting\Lookup;

use App\Models\Setting\LookupNamespace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý tra cứu');
    }

    public function prepareForValidation()
    {
        $namespaceId = $this->input('namespace_id');
        $finalNamespaceId = $namespaceId === '_null' ? null : $namespaceId;

        $this->merge([
            'slug' => Str::slug($this->input('slug') ?? $this->input('display_name')),
            'namespace_id' => $finalNamespaceId,
        ]);

        $namespace = LookupNamespace::find($finalNamespaceId);
        if (! $namespace || $namespace->slug !== 'mau-sac') {
            $metadata = $this->input('metadata', []);
            unset($metadata['hex_code']);
            $this->merge(['metadata' => $metadata]);
        }

        if (! $namespace || $namespace->slug !== 'danh-muc-phu') {
            $metadata = $this->input('metadata', []);
            unset($metadata['category_id']);
            $this->merge(['metadata' => $metadata]);
        }
    }

    public function rules(): array
    {
        $namespaceId = $this->input('namespace_id');
        $finalNamespaceId = $namespaceId === '_null' ? null : $namespaceId;

        return [
            'namespace_id' => ['nullable', 'string'],
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('lookups')
                    ->whereNull('deleted_at')
                    ->where(fn($q) => $q->where('namespace_id', $finalNamespaceId)),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'mimes:jpeg,png,gif,webp,application/svg+xml', 'max:10240'],
            'image_url' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'metadata.hex_code' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'metadata.category_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'namespace_id.required' => 'Vui lòng chọn danh mục.',
            'namespace_id.uuid' => 'Danh mục không hợp lệ.',
            'namespace_id.exists' => 'Danh mục đã chọn không tồn tại.',
            'slug.unique' => 'Khóa đã tồn tại trong danh mục được chọn.',
            'image.image' => 'Hình ảnh không hợp lệ.',
            'image.max' => 'Dung lượng hình ảnh không được vượt quá 2MB.',
        ];
    }
}

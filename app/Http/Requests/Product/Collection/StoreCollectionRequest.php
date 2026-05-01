<?php

namespace App\Http\Requests\Product\Collection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('Quản lý bộ sưu tập');
    }

    public function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?? $this->input('display_name')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('collections')->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'image_url' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Vui lòng nhập tên hiển thị cho bộ sưu tập.',
            'display_name.max' => 'Tên hiển thị không được vượt quá 255 ký tự.',

            'slug.required' => 'Vui lòng nhập đường dẫn (slug).',
            'slug.unique' => 'Đường dẫn này đã được sử dụng bởi một bộ sưu tập khác.',
            'slug.max' => 'Đường dẫn không được vượt quá 64 ký tự.',

            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png, hoặc webp.',
            'image.max' => 'Dung lượng hình ảnh không được vượt quá 2MB.',
        ];
    }
}

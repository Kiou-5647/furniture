<?php

namespace App\Http\Requests\Product\Category;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('categories.manage');
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
            'group_id' => ['required', 'exists:lookups,id'],
            'product_type' => ['required', Rule::enum(ProductType::class)],
            'room_id' => ['nullable', 'exists:lookups,id'],
            'display_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:64',
                Rule::unique('categories')->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required' => 'Vui lòng chọn nhóm danh mục.',
            'group_id.exists' => 'Nhóm danh mục đã chọn không hợp lệ.',

            'product_type.required' => 'Vui lòng chọn loại sản phẩm.',
            'product_type.enum' => 'Loại sản phẩm không hợp lệ.',

            'display_name.required' => 'Vui lòng nhập tên hiển thị cho danh mục.',
            'display_name.max' => 'Tên hiển thị không được vượt quá 255 ký tự.',

            'slug.required' => 'Vui lòng nhập đường dẫn (slug).',
            'slug.unique' => 'Đường dẫn này đã được sử dụng bởi một danh mục khác.',
            'slug.max' => 'Đường dẫn không được vượt quá 64 ký tự.',

            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpg, jpeg, png, hoặc webp.',
            'image.max' => 'Dung lượng hình ảnh không được vượt quá 2MB.',
        ];
    }
}

<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('products.manage');
    }

    public function prepareForValidation()
    {
        $this->merge([
            'features' => $this->input('features') ?? [],
            'specifications' => $this->normalizeSpecBooleans($this->input('specifications') ?? []),
            'care_instructions' => $this->input('care_instructions') ?? [],
            'search_keywords' => $this->input('search_keywords') ?? [],
            'option_groups' => $this->normalizeOptionGroupBooleans($this->input('option_groups') ?? []),
            'assembly_info' => $this->normalizeAssemblyBooleans($this->input('assembly_info') ?? []),
            'variants' => $this->normalizeVariantBooleans($this->input('variants') ?? []),
        ]);
    }

    protected function normalizeSpecBooleans(?array $specs): ?array
    {
        if (! $specs) {
            return $specs;
        }

        foreach ($specs as $key => $group) {
            if (isset($group['is_filterable'])) {
                $specs[$key]['is_filterable'] = filter_var($group['is_filterable'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $specs;
    }

    protected function normalizeOptionGroupBooleans(?array $groups): ?array
    {
        if (! $groups) {
            return $groups;
        }

        foreach ($groups as $i => $group) {
            if (isset($group['is_swatches'])) {
                $groups[$i]['is_swatches'] = filter_var($group['is_swatches'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $groups;
    }

    protected function normalizeAssemblyBooleans(?array $assembly): ?array
    {
        if (! $assembly) {
            return $assembly;
        }

        if (isset($assembly['required'])) {
            $assembly['required'] = filter_var($assembly['required'], FILTER_VALIDATE_BOOLEAN);
        }

        return $assembly;
    }

    protected function normalizeVariantBooleans(?array $variants): ?array
    {
        if (! $variants) {
            return $variants;
        }

        foreach ($variants as $i => $variant) {
            if (isset($variant['specifications']) && is_array($variant['specifications'])) {
                foreach ($variant['specifications'] as $key => $group) {
                    if (isset($group['is_filterable'])) {
                        $variants[$i]['specifications'][$key]['is_filterable'] = filter_var($group['is_filterable'], FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
        }

        return $variants;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'collection_id' => ['nullable', 'exists:collections,id'],
            'status' => ['required', Rule::in(['draft', 'pending_review', 'published', 'hidden', 'archived'])],
            'name' => ['required', 'string', 'max:255'],
            'features' => ['nullable', 'array'],
            'features.*.lookup_slug' => ['nullable', 'string'],
            'features.*.display_name' => ['required', 'string'],
            'features.*.description' => ['nullable', 'string'],
            'care_instructions' => ['nullable', 'array'],
            'care_instructions.*' => ['string'],
            'specifications' => ['nullable', 'array'],
            'specifications.*.is_filterable' => ['boolean'],
            'specifications.*.lookup_namespace' => ['nullable', 'string'],
            'specifications.*.items' => ['nullable', 'array'],
            'option_groups' => ['nullable', 'array'],
            'option_groups.*.name' => ['required_with:option_groups', 'string'],
            'option_groups.*.namespace' => ['required_with:option_groups', 'string'],
            'option_groups.*.is_swatches' => ['boolean'],
            'option_groups.*.options' => ['required_with:option_groups', 'array'],
            'option_groups.*.options.*.value' => ['required', 'string'],
            'option_groups.*.options.*.label' => ['required', 'string'],
            'filterable_options' => ['nullable', 'array'],
            'assembly_info' => ['nullable', 'array'],
            'assembly_info.required' => ['nullable', 'boolean'],
            'assembly_info.estimated_minutes' => ['nullable', 'integer', 'min:1'],
            'assembly_info.price' => ['nullable', 'numeric', 'min:0'],
            'assembly_info.instructions_url' => ['nullable', 'url'],
            'assembly_info.difficulty_level' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'warranty_months' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['boolean'],
            'is_dropship' => ['boolean'],
            'is_new_arrival' => ['boolean'],
            'is_custom_made' => ['boolean'],
            'published_date' => ['nullable', 'date'],
            'new_arrival_until' => ['nullable', 'date', 'after_or_equal:today'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'exists:product_variants,id'],
            'variants.*.sku' => [
                'required_with:variants',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1] ?? null;
                    $variantId = $this->input("variants.{$index}.id");
                    $query = DB::table('product_variants')->where('sku', $value);
                    if ($variantId) {
                        $query->where('id', '!=', $variantId);
                    }
                    if ($query->exists()) {
                        $fail('Mã SKU đã được sử dụng.');
                    }
                },
            ],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.compared_at_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.build_cost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.weight' => ['nullable', 'array'],
            'variants.*.weight.value' => ['required_with:variants.*.weight', 'numeric', 'min:0'],
            'variants.*.weight.unit' => ['required_with:variants.*.weight', Rule::in(['kg', 'lb'])],
            'variants.*.dimensions' => ['nullable', 'array'],
            'variants.*.dimensions.length' => ['required_with:variants.*.dimensions', 'numeric', 'min:0'],
            'variants.*.dimensions.width' => ['required_with:variants.*.dimensions', 'numeric', 'min:0'],
            'variants.*.dimensions.height' => ['required_with:variants.*.dimensions', 'numeric', 'min:0'],
            'variants.*.dimensions.unit' => ['required_with:variants.*.dimensions', Rule::in(['mm', 'cm', 'm', 'inch', 'ft'])],
            'variants.*.option_values' => ['nullable', 'array'],
            'variants.*.features' => ['nullable', 'array'],
            'variants.*.features.*.lookup_slug' => ['nullable', 'string'],
            'variants.*.features.*.display_name' => ['required', 'string'],
            'variants.*.features.*.description' => ['nullable', 'string'],
            'variants.*.specifications' => ['nullable', 'array'],
            'variants.*.specifications.*.is_filterable' => ['boolean'],
            'variants.*.specifications.*.lookup_namespace' => ['nullable', 'string'],
            'variants.*.specifications.*.items' => ['nullable', 'array'],
            'variants.*.care_instructions' => ['nullable', 'array'],
            'variants.*.care_instructions.*' => ['string'],
            'variants.*.status' => ['required_with:variants', Rule::in(['active', 'inactive'])],
            'variants.*.title' => ['nullable', 'string', 'max:255'],
            'variants.*.slug' => ['nullable', 'string', 'max:128'],
            'variants.*.description' => ['nullable', 'string'],
            'variants.*.primary_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'variants.*.hover_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'variants.*.gallery_files' => ['nullable', 'array'],
            'variants.*.gallery_files.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'variants.*.dimension_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'variants.*.swatch_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'variants.*.removed_gallery_ids' => ['nullable', 'array'],
            'variants.*.removed_gallery_ids.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'vendor_id.exists' => 'Nhà cung cấp đã chọn không hợp lệ.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'collection_id.exists' => 'Bộ sưu tập đã chọn không hợp lệ.',
            'features.*.display_name.required' => 'Tên tính năng là bắt buộc.',
            'variants.*.sku.required_with' => 'Mã SKU là bắt buộc cho mỗi biến thể.',
            'variants.*.sku.unique' => 'Mã SKU đã được sử dụng.',
            'variants.*.price.required_with' => 'Giá bán là bắt buộc cho mỗi biến thể.',
            'variants.*.price.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
            'features.*.string' => 'Mỗi tính năng phải là chuỗi ký tự.',
            'variants.*.features.*.display_name.required' => 'Tên tính năng biến thể là bắt buộc.',
            'variants.*.gallery_files.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'variants.*.gallery_files.*.max' => 'Dung lượng hình ảnh không được vượt quá 5MB.',
            'variants.*.primary_image_file.image' => 'Ảnh chính phải là hình ảnh.',
            'variants.*.hover_image_file.image' => 'Ảnh hover phải là hình ảnh.',
            'variants.*.dimension_image_file.image' => 'Bản vẽ kỹ thuật phải là hình ảnh.',
            'variants.*.swatch_image_file.image' => 'Ảnh swatch phải là hình ảnh.',
            'assembly_info.difficulty_level.in' => 'Mức độ lắp ráp không hợp lệ.',
            'new_arrival_until.after_or_equal' => 'Ngày kết thúc hàng mới phải từ hôm nay trở đi.',
        ];
    }
}

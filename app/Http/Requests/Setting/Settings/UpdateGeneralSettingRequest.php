<?php

namespace App\Http\Requests\Setting\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => 'required|string|max:255',
            'freeship_threshold' => 'required|numeric|min:0',
            'default_warranty' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'site_name.required' => 'Vui lòng nhập tên trang web.',
            'site_name.string' => 'Tên trang web phải là một chuỗi văn bản.',
            'site_name.max' => 'Tên trang web không được vượt quá 255 ký tự.',

            'freeship_threshold.required' => 'Vui lòng nhập ngưỡng miễn phí vận chuyển.',
            'freeship_threshold.numeric' => 'Ngưỡng miễn phí vận chuyển phải là một số.',
            'freeship_threshold.min' => 'Ngưỡng miễn phí vận chuyển không được nhỏ hơn 0.',

            'default_warranty.required' => 'Vui lòng nhập thời hạn bảo hành mặc định.',
            'default_warranty.integer' => 'Thời hạn bảo hành phải là một số nguyên.',
            'default_warranty.min' => 'Thời hạn bảo hành không được nhỏ hơn 0.',
        ];
    }
}

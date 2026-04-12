<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignerAvailabilitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('designers.manage');
    }

    public function rules(): array
    {
        return [
            'availabilities' => ['required', 'array'],
            'availabilities.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i', 'after:availabilities.*.start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'availabilities.required' => 'Lịch làm việc là bắt buộc.',
            'availabilities.*.day_of_week.required' => 'Ngày trong tuần là bắt buộc.',
            'availabilities.*.start_time.required' => 'Giờ bắt đầu là bắt buộc.',
            'availabilities.*.end_time.required' => 'Giờ kết thúc là bắt buộc.',
            'availabilities.*.end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ];
    }
}

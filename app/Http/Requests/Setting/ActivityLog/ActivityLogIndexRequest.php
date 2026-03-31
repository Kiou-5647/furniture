<?php

namespace App\Http\Requests\Setting\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Đảm bảo user có quyền xem nhật ký
        return $this->user()->can('activities.manage');
    }

    public function rules(): array
    {
        return [
            'subject_type' => ['required', 'string'],
            'subject_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_type.required' => 'Loại đối tượng không được để trống.',
            'subject_id.required' => 'ID đối tượng không được để trống.',
        ];
    }
}

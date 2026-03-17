<?php

namespace App\Http\Requests\Employee\Lookup;

use App\Models\Setting\Lookup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLookupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(
            'lookup.update',
            $this->route('id') ? Lookup::find($this->route('id')) : null
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $lookupId = $this->route('id');
        $namespace = $this->input('namespace');

        return [
            'namespace' => ['required', 'string', 'max:64', 'exists:lookups,namespace'],
            'key' => [
                'required',
                'string',
                'max:64',
                Rule::unique('lookups')
                    ->ignore($lookupId)
                    ->where(fn ($q) => $q->where('namespace', $namespace)),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.unique' => 'Khóa đã tồn tại trong namespace được chọn.',
        ];
    }
}

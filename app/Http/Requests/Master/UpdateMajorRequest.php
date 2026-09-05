<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $majorId = $this->route('major')?->id ?? $this->route('major');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('majors', 'name')->ignore($majorId)],
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('majors', 'code')->ignore($majorId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama jurusan sudah digunakan.',
            'code.unique' => 'Kode jurusan sudah digunakan.',
        ];
    }
}

<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'code.required' => 'Kode mata pelajaran wajib diisi.',
            'code.unique' => 'Kode mata pelajaran sudah digunakan.',
        ];
    }
}

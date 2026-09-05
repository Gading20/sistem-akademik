<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:majors,name'],
            'code' => ['required', 'string', 'max:50', 'unique:majors,code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jurusan wajib diisi.',
            'name.unique' => 'Nama jurusan sudah digunakan.',
            'code.required' => 'Kode jurusan wajib diisi.',
            'code.unique' => 'Kode jurusan sudah digunakan.',
        ];
    }
}

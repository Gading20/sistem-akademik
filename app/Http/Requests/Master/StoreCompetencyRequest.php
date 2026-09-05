<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'major_id' => ['required', 'exists:majors,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'major_id.required' => 'Jurusan wajib dipilih.',
            'major_id.exists' => 'Jurusan tidak ditemukan.',
            'name.required' => 'Nama kompetensi keahlian wajib diisi.',
            'code.required' => 'Kode kompetensi wajib diisi.',
        ];
    }
}

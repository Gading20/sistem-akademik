<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'name' => ['sometimes', 'string', 'in:ganjil,genap'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'name.in' => 'Nama semester harus ganjil atau genap.',
            'end_date.after' => 'Tanggal akhir harus setelah tanggal mulai.',
        ];
    }
}

<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:academic_years,name'],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['required', 'integer', 'min:2000', 'max:2100', 'after:start_year'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.unique' => 'Nama tahun ajaran sudah digunakan.',
            'start_year.required' => 'Tahun mulai wajib diisi.',
            'start_year.integer' => 'Tahun mulai harus berupa angka.',
            'start_year.min' => 'Tahun mulai minimal 2000.',
            'start_year.max' => 'Tahun mulai maksimal 2100.',
            'end_year.required' => 'Tahun selesai wajib diisi.',
            'end_year.integer' => 'Tahun selesai harus berupa angka.',
            'end_year.min' => 'Tahun selesai minimal 2000.',
            'end_year.max' => 'Tahun selesai maksimal 2100.',
            'end_year.after' => 'Tahun selesai harus setelah tahun mulai.',
        ];
    }
}

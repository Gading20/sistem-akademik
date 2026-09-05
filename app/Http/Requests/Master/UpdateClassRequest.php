<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'major_id' => ['sometimes', 'exists:majors,id'],
            'competency_id' => ['nullable', 'exists:competencies,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'semester_id' => ['sometimes', 'exists:semesters,id'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'major_id.exists' => 'Jurusan tidak ditemukan.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
        ];
    }
}

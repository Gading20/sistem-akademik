<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeachingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'class_ids' => ['sometimes', 'array', 'min:1'],
            'class_ids.*' => ['exists:classes,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'semester_id' => ['sometimes', 'exists:semesters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_ids.min' => 'Pilih minimal satu kelas.',
            'class_ids.*.exists' => 'Kelas tidak ditemukan.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
        ];
    }
}

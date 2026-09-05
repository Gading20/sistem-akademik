<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_ids.required' => 'Kelas wajib dipilih.',
            'class_ids.min' => 'Pilih minimal satu kelas.',
            'class_ids.*.exists' => 'Kelas tidak ditemukan.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.required' => 'Semester wajib dipilih.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
        ];
    }
}

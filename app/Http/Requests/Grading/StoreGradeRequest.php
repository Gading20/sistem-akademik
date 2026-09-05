<?php

namespace App\Http\Requests\Grading;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'tugas_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quiz_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uts_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uas_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'practical_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'project_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_remedial' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.required' => 'Semester wajib dipilih.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
            'tugas_score.numeric' => 'Nilai tugas harus berupa angka.',
            'tugas_score.min' => 'Nilai tugas minimal 0.',
            'tugas_score.max' => 'Nilai tugas maksimal 100.',
            'quiz_score.numeric' => 'Nilai quiz harus berupa angka.',
            'uts_score.numeric' => 'Nilai UTS harus berupa angka.',
            'uas_score.numeric' => 'Nilai UAS harus berupa angka.',
            'practical_score.numeric' => 'Nilai praktik harus berupa angka.',
            'project_score.numeric' => 'Nilai proyek harus berupa angka.',
        ];
    }
}

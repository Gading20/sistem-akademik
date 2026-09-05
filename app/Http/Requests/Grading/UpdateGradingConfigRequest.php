<?php

namespace App\Http\Requests\Grading;

use App\Enums\GradingMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradingConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'class_id' => ['sometimes', 'exists:classes,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'semester_id' => ['sometimes', 'exists:semesters,id'],
            'method' => ['sometimes', 'string', Rule::in(array_column(GradingMethodEnum::cases(), 'value'))],
            'tugas_weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'quiz_weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'uts_weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'uas_weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'practical_weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'project_weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
            'method.in' => 'Metode penilaian tidak valid.',
        ];
    }
}

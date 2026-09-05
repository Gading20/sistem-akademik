<?php

namespace App\Http\Requests\Grading;

use App\Enums\GradingMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradingConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'method' => ['required', 'string', Rule::in(array_column(GradingMethodEnum::cases(), 'value'))],
            'tugas_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'quiz_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'uts_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'uas_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'practical_weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'project_weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.required' => 'Semester wajib dipilih.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
            'method.required' => 'Metode penilaian wajib dipilih.',
            'method.in' => 'Metode penilaian tidak valid.',
            'tugas_weight.required' => 'Bobot tugas wajib diisi.',
            'tugas_weight.numeric' => 'Bobot tugas harus berupa angka.',
            'tugas_weight.min' => 'Bobot tugas minimal 0.',
            'tugas_weight.max' => 'Bobot tugas maksimal 100.',
            'quiz_weight.required' => 'Bobot quiz wajib diisi.',
            'quiz_weight.numeric' => 'Bobot quiz harus berupa angka.',
            'uts_weight.required' => 'Bobot UTS wajib diisi.',
            'uts_weight.numeric' => 'Bobot UTS harus berupa angka.',
            'uas_weight.required' => 'Bobot UAS wajib diisi.',
            'uas_weight.numeric' => 'Bobot UAS harus berupa angka.',
        ];
    }
}

<?php

namespace App\Http\Requests\Grading;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tugas_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quiz_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uts_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uas_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'practical_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'project_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
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

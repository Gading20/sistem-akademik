<?php

namespace App\Http\Requests\Exam;

use App\Enums\ExamTypeEnum;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user && $user->hasRole(RoleEnum::GURU->value) && $user->teacher) {
            $this->merge([
                'teacher_id' => $user->teacher->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'semester_id' => ['sometimes', 'exists:semesters,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', Rule::in(array_column(ExamTypeEnum::cases(), 'value'))],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after:start_at'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5'],
            'attempt_limit' => ['sometimes', 'integer', 'min:1'],
            'random_question' => ['sometimes', 'boolean'],
            'random_option' => ['sometimes', 'boolean'],
            'shuffle_options' => ['sometimes', 'boolean'],
            'show_result' => ['sometimes', 'boolean'],
            'show_answer_after_submit' => ['sometimes', 'boolean'],
            'passing_score' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', 'in:draft,published,active,completed,archived'],
            'class_ids' => ['sometimes', 'array', 'min:1'],
            'class_ids.*' => ['exists:classes,id'],
            'questions' => ['nullable', 'array'],
            'questions.*' => ['integer', 'distinct', 'exists:questions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
            'type.in' => 'Jenis ujian tidak valid.',
            'end_at.after' => 'Waktu selesai harus setelah waktu mulai.',
            'duration_minutes.min' => 'Durasi ujian minimal 5 menit.',
            'class_ids.*.exists' => 'Kelas tidak ditemukan.',
            'questions.*.integer' => 'Soal yang dipilih tidak valid.',
            'questions.*.distinct' => 'Soal tidak boleh dipilih lebih dari satu kali.',
            'questions.*.exists' => 'Soal tidak ditemukan.',
        ];
    }
}
